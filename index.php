<?php
require 'Slim/Slim.php';
require 'php-cassandra/php-cassandra.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

header("Access-Control-Allow-Origin: *");
header("Content-Type: text/json");

function query($ks, $q) {
	$nodes = ['127.0.0.1'];

	$connection = new Cassandra\Connection($nodes, $ks);
	$connection->connect();

	//$tables = ["peers", "schema_triggers", "batchlog", "local", "range_xfers", "sstable_activity", "size_estimates", "hints", "schema_keyspaces", "peer_events", "compaction_history", "schema_columns", "schema_usertypes", "compactions_in_progress", "paxos", "schema_columnfamilies"];
	//$tables = ["peers", "local", "schema_keyspaces", "compaction_history", "schema_columns", "compactions_in_progress", "schema_columnfamilies"];

	$args = [];
	$response = $connection->querySync($q, $args);
	$rows = $response->fetchAll();
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
	foreach( $rows as $row ) {
		$keyspace = ["id" => $id++, "name" => $row["keyspace_name"], "durable_writes" => $row["durable_writes"], "strategy_class" => $row["strategy_class"], "strategy_options" => $row["strategy_options"], "tables" => []];
		$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$row["keyspace_name"]."'";
		$rowst = query('system', $qt);
		$idt = 0;
		foreach( $rowst as $rowt ) {
			//$table = ["id" => $idt++, "name" => $rowt["columnfamily_name"]] + $rowt;
			$keyspace["tables"][] = $row["keyspace_name"].":".$rowt["columnfamily_name"];
		}
		$keyspaces[] = $keyspace;
	}
	echo json_encode(["keyspaces" => $keyspaces]);
});

$app->get('/books/:keyspace_table', function ($keyspace_table) {
	list($keyspace, $table) = explode(":", $keyspace_table);
	//$qt = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table."'";
	$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table."'";
	$rowst = query('system', $qt);
	$idt = 0;
	$tables = [];
	foreach( $rowst as $rowt ) {
		$rowtCC = [];
		foreach( $rowt as $k=>$v ) {
			$rowtCC[lcfirst(preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$k))] = $v;
		}
		$table = ["id" => $keyspace.":".$rowt["columnfamily_name"], "name" => $rowt["columnfamily_name"]] + $rowtCC;
		//$tables[] = $table;
	}
	echo json_encode(array("book" => $table));
});

/*
$app->get('/books/:keyspace_id', function ($keyspace_id) {
	$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$keyspace."'";
	$rowst = query('system', $qt);
	$idt = 0;
	$tables = [];
	foreach( $rowst as $rowt ) {
		$tab = ["id" => $keyspace.$rowt["columnfamily_name"], "name" => $rowt["columnfamily_name"]] + $rowt;
		$tables[] = $tab;
	}
	echo json_encode(array("books" => $tables, "book" => $tables[0]));
});
$app->get('/tables/:keyspace', function ($keyspace) {
	$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$keyspace."'";
	$rowst = query('system', $qt);
	$idt = 0;
	$tables = [];
	foreach( $rowst as $rowt ) {
		$table = ["id" => $idt++, "name" => $rowt["columnfamily_name"]] + $rowt;
		$tables[] = $table;
	}
	echo json_encode($tables);
});
$app->get('/tables/:keyspace/:table', function ($keyspace, $table) {
	$qt = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table."'";
	$rowst = query('system', $qt);
	$idt = 0;
	$tables = [];
	foreach( $rowst as $rowt ) {
		$table = ["id" => $idt++, "name" => $rowt["columnfamily_name"]] + $rowt;
		$tables[] = $table;
	}
	echo json_encode($tables);
});
*/

$app->get('/:name', function ($name) {
    echo $name;
});

$app->run();
