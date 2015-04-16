<?php
require 'Slim/Slim.php';
require 'php-cassandra/php-cassandra.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
//header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Headers: X-Requested-With');
header('Access-Control-Allow-Headers: Content-Type');
//header("Content-Type: text/json");

function query($ks, $q) {
	$nodes = ['127.0.0.1'];

	$connection = new Cassandra\Connection($nodes, $ks);
	$connection->connect();

	//$tables = ["peers", "schema_triggers", "batchlog", "local", "range_xfers", "sstable_activity", "size_estimates", "hints", "schema_keyspaces", "peer_events", "compaction_history", "schema_columns", "schema_usertypes", "compactions_in_progress", "paxos", "schema_columnfamilies"];
	//$tables = ["peers", "local", "schema_keyspaces", "compaction_history", "schema_columns", "compactions_in_progress", "schema_columnfamilies"];

	$args = [];
	$response = $connection->querySync($q, $args);
	if( substr($q, 0, 6) == "INSERT" ) {
		$rows = $response;
	} else {
		$rows = $response->fetchAll();
	}
	return $rows;
}

/*
$app->get('/users(/:ks)', function ($ks=0) {
	// keyspace_name | durable_writes | strategy_class                              | strategy_options
	$q = "SELECT * FROM schema_keyspaces";
	$rows = query('system', $q);
	$keyspaces = [];
	$id = 0;
	foreach( $rows as $row ) {
		$keyspace = ["id" => $id, "name" => $row["keyspace_name"], "durable_writes" => $row["durable_writes"], "strategy_class" => $row["strategy_class"], "strategy_options" => $row["strategy_options"], "tables" => []];
		$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$row["keyspace_name"]."'";
		$rowst = query('system', $qt);
		$idt = 0;
		foreach( $rowst as $rowt ) {
			//$table = ["id" => $idt++, "name" => $rowt["columnfamily_name"]] + $rowt;
			$keyspace["tables"][] = $row["keyspace_name"].":".$rowt["columnfamily_name"];
		}
		$keyspace["first_name"] = "hey";
		$keyspace["last_name"] = "heylast";
		$keyspace["bio"] = "bio";
		$keyspaces[$id++] = $keyspace;
	}
	if( $ks == 0 ) {
		echo json_encode(array("users" => $keyspaces));
	} else {
		echo json_encode(array("user" => $keyspaces[$ks-1]));
	}
});
*/

$app->get('/keyspaces', function () {
	// keyspace_name | durable_writes | strategy_class                              | strategy_options
	$q = "SELECT * FROM schema_keyspaces";
	$rows = query('system', $q);
	$keyspaces = [];
	$id = 0;
$table_keys = [];
	foreach( $rows as $row ) {
		$keyspace = ["id" => $row["keyspace_name"], "name" => $row["keyspace_name"], "durable_writes" => $row["durable_writes"], "strategy_class" => $row["strategy_class"], "strategy_options" => $row["strategy_options"], "tables" => []];
		$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$row["keyspace_name"]."'";
		$rowst = query('system', $qt);
		$idt = 0;
		foreach( $rowst as $rowt ) {
			$table_keys[] = $row["keyspace_name"].":".$rowt["columnfamily_name"];
			$keyspace["tables"][] = $row["keyspace_name"].":".$rowt["columnfamily_name"];
		}
		$keyspaces[] = $keyspace;
	}


	$tables = [];
	foreach( $table_keys as $keyspace_table ) {
	list($keyspace, $table_name) = explode(":", $keyspace_table);
	$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."'";
	$rowst = query('system', $qt);
	$rowt = $rowst[0];
	$rowtCC = [];
	foreach( $rowt as $k=>$v ) {
		$v = str_replace("org.apache.cassandra.db.", "", $v);
		$rowtCC[lcfirst(preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$k))] = $v;
	}

	$table = ["id" => $keyspace.":".$rowt["columnfamily_name"], "name" => $rowt["columnfamily_name"]] + $rowtCC;

	$q = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."'";
	$rows = query('system', $q);
	$table["columns"] = [];
	foreach( $rows as $k=>$v ) {
		$table["columns"][] = $keyspace_table.":".$v["column_name"];
	}
	$tables[] = $table;
	}

	echo json_encode(["keyspaces" => $keyspaces, "tables" => $tables]);
});

$app->post('/columns', function () use ($app) {
	$app->contentType('application/json');
	$postdata = file_get_contents("php://input");
	$payload = json_decode($postdata, true);
	echo json_encode(["column" => ["id" => "new-column", "columnName" => $payload["column"]["columnName"]]]);
});

$app->options('/columns', function () use ($app) {
	$app->contentType('application/json');
	echo json_encode([]);
});

$app->post('/tables', function () use ($app) {
	$app->contentType('application/json');
	$postdata = file_get_contents("php://input");
	$payload = json_decode($postdata, true);
	echo json_encode(["table" => ["id" => "new-table", "name" => $payload["table"]["name"]]]);
});

$app->options('/tables', function () use ($app) {
	$app->contentType('application/json');
	echo json_encode([]);
});

$app->get('/tables/:keyspace_table', function ($keyspace_table) {
	list($keyspace, $table_name) = explode(":", $keyspace_table);
	$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."'";
	$rowst = query('system', $qt);
	$rowt = $rowst[0];
	$rowtCC = [];
	foreach( $rowt as $k=>$v ) {
		$v = str_replace("org.apache.cassandra.db.", "", $v);
		$rowtCC[lcfirst(preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$k))] = $v;
	}

	$table = ["id" => $keyspace.":".$rowt["columnfamily_name"], "name" => $rowt["columnfamily_name"]] + $rowtCC;

	$q = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."'";
	$rows = query('system', $q);
	$table["columns"] = [];
	foreach( $rows as $k=>$v ) {
		$table["columns"][] = $keyspace_table.":".$v["column_name"];
	}

	echo json_encode(array("table" => $table));
});

$app->get('/columns/:keyspace_table_column', function ($keyspace_table_column) {
	list($keyspace, $table_name, $column_name) = explode(":", $keyspace_table_column);
	$q = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."' AND column_name = '".$column_name."'";
	$rows = query('system', $q);
	$row = $rows[0];
	$rowCC = [];
	foreach( $row as $k=>$v ) {
		$v = str_replace("org.apache.cassandra.db.", "", $v);
		$rowCC[lcfirst(preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$k))] = $v;
	}
	$column = ["id" => $keyspace_table_column] + $rowCC;
	echo json_encode(array("column" => $column));
});

$app->get('/results', function () {
	$table = $_GET["table"];
	$limit = $_GET["limit"];
	$q = "SELECT * FROM ".$table." LIMIT ".$limit;
	$rows = query('system', $q);
	$results = [];
	foreach( $rows as $row ) {
		$line["cols"] = [];
		$columns = [];
		foreach( $row as $k => $v ) {
			$line["cols"][] = $v;
			$columns[] = $k;
		}
		$results[] = $line;
	}
	echo json_encode(["rows" => $results, "columns" => $columns]);
});

$app->get('/query', function () {
	$q = $_GET["q"];
	$rows = query('system', $q);
	if( substr($q, 0, 6) == "INSERT" ) {
		$ret = ["rows" => [["cols" => ["success"]]], "columns" => ["status"]];
	} else {
		$results = [];
		$columns = [];
		foreach( $rows as $row ) {
			$line["cols"] = [];
			$columns = [];
			foreach( $row as $k => $v ) {
				$line["cols"][] = $v;
				$columns[] = $k;
			}
			$results[] = $line;
		}
		$ret = ["rows" => $results, "columns" => $columns];
	}
	echo json_encode($ret);
});

$app->get('/:name', function ($name) {
    echo $name;
});

$app->run();
