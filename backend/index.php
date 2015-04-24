<?php
$config_filename = "config.local.php";
if( file_exists($config_filename) ) {
	$config = require $config_filename;
} else {
	$config = require "config.default.php";
}

require 'Slim/Slim.php';
require 'php-cassandra/php-cassandra.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->contentType('application/json');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header('Access-Control-Allow-Headers: X-Requested-With');
header('Access-Control-Allow-Headers: Content-Type');

function query($ks, $q) {
	global $config;
	$nodes = $config["cassandra"]["nodes"];

	$connection = new Cassandra\Connection($nodes, $ks);
	$connection->connect();

	//$tables = ["peers", "schema_triggers", "batchlog", "local", "range_xfers", "sstable_activity", "size_estimates", "hints", "schema_keyspaces", "peer_events", "compaction_history", "schema_columns", "schema_usertypes", "compactions_in_progress", "paxos", "schema_columnfamilies"];
	//$tables = ["peers", "local", "schema_keyspaces", "compaction_history", "schema_columns", "compactions_in_progress", "schema_columnfamilies"];

	$args = [];
	$response = $connection->querySync($q, $args);
	return $response;
}
function queryFetch($ks, $q) {
	$response = query($ks, $q);
	$rows = $response->fetchAll();
	$rows = json_decode(json_encode($rows), true);
	return $rows;
}


/**
 * Keyspaces
 */
$app->get('/keyspaces', function () {
	$q = "SELECT * FROM schema_keyspaces";
	$rows = queryFetch('system', $q);
	$keyspaces = [];
	$id = 0;
$table_keys = [];
	foreach( $rows as $row ) {
		$keyspace = ["id" => $row["keyspace_name"], "name" => $row["keyspace_name"], "durable_writes" => $row["durable_writes"], "strategy_class" => $row["strategy_class"], "strategy_options" => $row["strategy_options"], "tables" => []];
		$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$row["keyspace_name"]."'";
		$rowst = queryFetch('system', $qt);
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
		$rowst = queryFetch('system', $qt);
		$rowt = $rowst[0];
		$rowtCC = [];
		foreach( $rowt as $k=>$v ) {
			$v = str_replace("org.apache.cassandra.db.", "", $v);
			$rowtCC[camelCase($k)] = $v;
		}

		$table = ["id" => $keyspace.":".$rowt["columnfamily_name"], "name" => $rowt["columnfamily_name"]] + $rowtCC;

		$rows = getColumns($keyspace_table);
		$table["columns"] = [];
		foreach( $rows as $k=>$v ) {
			$table["columns"][] = $keyspace_table.":".$v["columnName"];
		}
		$tables[] = $table;
	}

	echo json_encode(["keyspaces" => $keyspaces, "tables" => $tables]);
});


/**
 * Tables
 */
$app->post('/tables', function () {
	$postdata = file_get_contents("php://input");
	$payload = json_decode($postdata, true);
	echo json_encode(["table" => ["id" => "new-table", "name" => $payload["table"]["name"]]]);
});

$app->options('/tables', function () {
	echo json_encode([]);
});

$app->get('/tables/:keyspace_table', function ($keyspace_table) {
	list($keyspace, $table_name) = explode(":", $keyspace_table);
	$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."'";
	$rowst = queryFetch('system', $qt);
	$rowt = $rowst[0];
	$rowtCC = [];
	foreach( $rowt as $k=>$v ) {
		$v = str_replace("org.apache.cassandra.db.", "", $v);
		$rowtCC[camelCase($k)] = $v;
	}

	$table = ["id" => $keyspace.":".$rowt["columnfamily_name"], "name" => $rowt["columnfamily_name"]] + $rowtCC;

	$rows = getColumns($keyspace_table);
	//print_r($rows);
	$table["columns"] = [];
	foreach( $rows as $k=>$v ) {
		$table["columns"][] = $keyspace_table.":".$v["columnName"];
	}

	echo json_encode(array("table" => $table));
});

function camelCase($in) {
	return lcfirst(preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$in));
}
/**
 * Columns
 */
function getColumns($keyspace_table) {
	list($keyspace, $table_name) = explode(":", $keyspace_table);
	$q = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."'";
	$rows = queryFetch('system', $q);
	$columns = [];
	foreach( $rows as $row ) {
		$rowCC = [];
		foreach( $row as $k=>$v ) {
			$v = str_replace("org.apache.cassandra.db.", "", $v);
			$rowCC[camelCase($k)] = $v;
		}
		$column = ["id" => $keyspace_table.":".$rowCC["columnName"]] + $rowCC;
		$columns[] = $column;
	}
	usort($columns, function($a, $b) {
		if( $a["type"] == $b["type"] ) {
			return $a["componentIndex"] > $b["componentIndex"];
		}
		if( $a["type"] == "partition_key" ) return -1;
		if( $b["type"] == "partition_key" ) return 1;
		if( $a["type"] == "clustering_key" ) return -1;
		if( $b["type"] == "clustering_key" ) return 1;
		return 1;
	});
	return $columns;
}
function getColumn($keyspace_table_column) {
	list($keyspace, $table_name, $column_name) = explode(":", $keyspace_table_column);
	$q = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table_name."' AND column_name = '".$column_name."'";
	$rows = queryFetch('system', $q);
	$row = $rows[0];
	$rowCC = [];
	foreach( $row as $k=>$v ) {
		$v = str_replace("org.apache.cassandra.db.", "", $v);
		$rowCC[camelCase($k)] = $v;
	}
	$column = ["id" => $keyspace_table_column] + $rowCC;
	return $column;
}

$app->get('/columns/:keyspace_table_column', function ($keyspace_table_column) {
	$column = getColumn($keyspace_table_column);
	echo json_encode(array("column" => $column));
});

$app->post('/columns', function () {
	$postdata = file_get_contents("php://input");
	$payload = json_decode($postdata, true);
	echo json_encode(["column" => ["id" => "new-column", "columnName" => $payload["column"]["columnName"]]]);
});

$app->options('/columns', function () {
	echo json_encode([]);
});


/**
 * Queries
 */
$app->get('/results', function () {
	$table = $_GET["table"];
	$limit = $_GET["limit"];
	$q = "SELECT * FROM ".$table." LIMIT ".$limit;
	$rows = queryFetch('system', $q);
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

$app->get('/query(/:keyspace/:table)', function ($keyspace="", $table="") {
	try {
		$q = trim($_GET["q"]);

		$is_select = preg_match("#^SELECT\s+[a-zA-Z0-9\*_-]+\s+FROM\s+([a-zA-Z0-9\._-]+)#i", $q, $matches);
		if( $is_select ) {
			$keyspace_table = $matches[1];
			if( !strstr($keyspace_table, ".") ) {
				throw new Exception("Invalid keyspace/table:[".$keyspace_table."]");
			}
			list($keyspace, $table) = explode(".", $keyspace_table);
			if( $keyspace == "" || $table == "" ) {
				throw new Exception("Invalid keyspace:[".$keyspace."] or table:[".$table."]");
			}
		}

		if( $is_select ) {
			$rows = queryFetch('system', $q);
			$results = [];
			$columns_names = [];
			foreach( $rows as $row ) {
				$line["cols"] = [];
				$columns = [];
				foreach( $row as $k => $v ) {
					$line["cols"][] = $v;
					$keyspace_table_column = $keyspace.":".$table.":".$k;
					$columns_names[$keyspace_table_column] = $keyspace_table_column;
				}
				$results[] = $line;
			}
			$columns = [];
			foreach( $columns_names as $keyspace_table_column ) {
				$columns[] = getColumn($keyspace_table_column);
			}
			$ret = ["status" => 0, "rows" => $results, "columns" => $columns, "keyspace" => $keyspace, "table" => $table, "message" => "OK [".count($rows)."] rows"];
		} else {
			query('system', $q);
			$ret = ["status" => 0, "rows" => [["cols" => ["success"]]], "columns" => ["status"], "message" => "OK"];
		}
	} catch( Exception $e ) {
		$ret = ["status" => 1, "message" => $e->getMessage()];
	}
	echo json_encode($ret);
});

$app->get('/:name', function ($name) {
    echo $name;
});

$app->run();
