<?php
require 'Slim/Slim.php';
require 'php-cassandra/php-cassandra.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

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

$app->get('/keyspaces', function () {
	// keyspace_name | durable_writes | strategy_class                              | strategy_options
	$q = "SELECT * FROM schema_keyspaces";
	$rows = query('system', $q);
	$keyspaces = [];
	$id = 0;
	foreach( $rows as $row ) {
		$keyspace = ["id" => $id++, "name" => $row["keyspace_name"], "durable_writes" => $row["durable_writes"], "strategy_class" => $row["strategy_class"], "strategy_options" => $row["strategy_options"], "tables" => []];
		//  keyspace_name | columnfamily_name       | bloom_filter_fp_chance | caching                                     | cf_id                                | column_aliases                           | comment                                | compaction_strategy_class                                       | compaction_strategy_options | comparator                                                                                                                                                                                                                                                                                                                                                                                                   | compression_parameters                                                   | default_time_to_live | default_validator                         | dropped_columns | gc_grace_seconds | index_interval | is_dense | key_aliases                                        | key_validator                                                                                                                                                              | local_read_repair_chance | max_compaction_threshold | max_index_interval | memtable_flush_period_in_ms | min_compaction_threshold | min_index_interval | read_repair_chance | speculative_retry | subcomparator | type     | value_alias

		$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$row["keyspace_name"]."'";
		$rowst = query('system', $qt);
		$idt = 0;
		foreach( $rowst as $rowt ) {
			$table = ["id" => $idt++, "name" => $rowt["columnfamily_name"]] + $rowt;
			$keyspace["tables"][] = $table;
		}
		$keyspaces[] = $keyspace;
	}
	echo json_encode($keyspaces);
});


$app->get('/keyspac', function () {
	$keyspaces = [["id" => 1, "name" => "system", "tables" => [["id" => 1, "name" => "peers"], ["id" => 2, "name" => "schema_columns"]]], ["id" => 2, "name" => "test", "tables" => []]];
	echo json_encode($keyspaces);
});

$app->get('/:name', function ($name) {
    echo $name;
});

$app->get('/', function () {
echo '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Rock & Roll</title>
  <link rel="stylesheet" href="/css/normalize.css">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="/css/bootstrap.css">
</head>
<body>
  <script type="text/x-handlebars">
    <div class="container">
      <div class="page-header">
        {{#link-to "index"}}
          <h1>Rock & Roll<small> with Ember.js</small></h1>
        {{/link-to}}
      </div>
      <div class="row">
        {{outlet}}
      </div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="loading">
    <div class="loading-pane">
      <div class="loading-message">
        Loading stuff, please have a cold beer.
        <div class="spinner"></div>
      </div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="keyspaces">
    <div class="col-md-4">
      <div class="list-group">
        <div class="list-group-item">
          {{input type="text" class="new-keyspace" placeholder="New Keyspace" value=newName insert-newline="createKeyspace" }}
          <button class="btn btn-primary btn-sm new-keyspace-button" {{action "createKeyspace"}}
            {{bind-attr disabled=disabled}}>Add</button>
        </div>
        {{#each model}}
          {{#link-to "keyspace.tables" this class="list-group-item keyspace-link"}}
            {{name}}
            <span class="pointer glyphicon glyphicon-chevron-right"></span>
          {{/link-to}}
        {{/each}}
      </div>
    </div>
    <div class="col-md-8">
      <div class="list-group">
        {{outlet}}
      </div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="keyspaces/index">
    <div class="list-group-item empty-list">
      <div class="empty-message">
        Select a keyspace.
      </div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="keyspace">
    {{outlet}}
  </script>

  <script type="text/x-handlebars" data-template-name="keyspace/loading">
    <div class="loading-pane">
      <div class="loading-message">
        Loading the keyspace, please have an organic orange juice.
      </div>
      <div class="spinner"></div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="keyspace/tables">
    {{#each model}}
      <div class="list-group-item">
        {{name}}
      </div>
    {{/each}}
  </script>

  <script src="/js/libs/jquery-1.11.2.min.js"></script>
  <script src="/js/libs/ember-template-compiler.js"></script>
  <script src="/js/libs/ember.min.js"></script>
  <script src="/js/libs/ic-ajax.js"></script>
  <script src="/js/libs/bootstrap.js"></script>
  <script src="/js/app.js"></script>
</body>
</html>';
});


$app->run();
