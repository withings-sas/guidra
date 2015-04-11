<?php
require 'Slim/Slim.php';
require 'php-cassandra/php-cassandra.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

header("Access-Control-Allow-Origin: *");

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

$app->get('/users(/:ks)', function ($ks=0) {
	// keyspace_name | durable_writes | strategy_class                              | strategy_options
	$q = "SELECT * FROM schema_keyspaces";
	$rows = query('system', $q);
	$keyspaces = [];
	$id = 0;
	foreach( $rows as $row ) {
		$keyspace = ["id" => $row["keyspace_name"], "name" => $row["keyspace_name"], "durable_writes" => $row["durable_writes"], "strategy_class" => $row["strategy_class"], "strategy_options" => $row["strategy_options"], "tables" => []];
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


$app->get('/keyspaces', function () {
	// keyspace_name | durable_writes | strategy_class                              | strategy_options
	$q = "SELECT * FROM schema_keyspaces";
	$rows = query('system', $q);
	$keyspaces = [];
	$id = 0;
	foreach( $rows as $row ) {
		$keyspace = ["id" => $id++, "name" => $row["keyspace_name"], "durable_writes" => $row["durable_writes"], "strategy_class" => $row["strategy_class"], "strategy_options" => $row["strategy_options"], "tables" => []];
		/*
		$qt = "SELECT * FROM schema_columnfamilies WHERE keyspace_name = '".$row["keyspace_name"]."'";
		$rowst = query('system', $qt);
		$idt = 0;
		foreach( $rowst as $rowt ) {
			$table = ["id" => $idt++, "name" => $rowt["columnfamily_name"]] + $rowt;
			$keyspace["tables"][] = $table;
		}
		*/
		$keyspaces[] = $keyspace;
	}
	echo json_encode($keyspaces);
});

$app->get('/books/:keyspace_table', function ($keyspace_table) {
	list($keyspace, $table) = explode(":", $keyspace_table);
	$qt = "SELECT * FROM schema_columns WHERE keyspace_name = '".$keyspace."' AND columnfamily_name = '".$table."'";
	$rowst = query('system', $qt);
	$idt = 0;
	$tables = [];
	foreach( $rowst as $rowt ) {
		$table = ["id" => $idt++, "name" => $rowt["columnfamily_name"]] + $rowt;
		$tables[] = $table;
	}
	echo json_encode(array("books" => $tables));
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

$app->get('/keyspac', function () {
	$keyspaces = [["id" => 1, "name" => "system", "tables" => [["id" => 1, "name" => "peers"], ["id" => 2, "name" => "schema_columns"]]], ["id" => 2, "name" => "test", "tables" => []]];
	echo json_encode($keyspaces);
});

$app->get('/:name', function ($name) {
    echo $name;
});

$app->get('/', function () {
echo '<html>
<head>
  <meta charset="utf-8">
  <title>Cassandra Explorer</title>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="/css/bootstrap.css">
</head>
<body>
  <script type="text/x-handlebars">
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a>Cassandra Explorer</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Settings</a></li>
          </ul>
          <form class="navbar-form navbar-right">
            <input type="text" class="form-control" placeholder="Query...">
          </form>
        </div>
      </div>
    </nav>
    <div class="container-fluid" style="margin-top:80px;">
      <div class="row">
        {{outlet "keyspaces"}}
      </div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="loading">
    <div class="loading-pane">
      <div class="loading-message">
        Loading...
        <div class="spinner"></div>
      </div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="keyspaces">
        <div class="col-sm-3 col-md-2 sidebar">
    <div class="col-md-4">
      <div class="list-group">
        {{#each model}}
          {{#link-to "keyspaces.keyspace" this class="list-group-item keyspace-link"}}
            {{name}}
          {{/link-to}}
        {{/each}}
      </div>
    </div>
    <div class="col-md-4">
      <div class="list-group">
        {{outlet "tables"}}
      </div>
    </div>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <h1 class="page-header">Dashboard</h1>

          <h2 class="sub-header">Section title</h2>
          <div class="table-responsive">
            {{outlet "details"}}
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

  <script type="text/x-handlebars" data-template-name="keyspace/loading">
    <div class="loading-pane">
      <div class="loading-message">
        Loading the keyspace, please have an organic orange juice.
      </div>
      <div class="spinner"></div>
    </div>
  </script>

  <script type="text/x-handlebars" data-template-name="keyspaces/keyspace">
    <ul class="nav nav-sidebar">
    {{#each model}}
      <li>{{#link-to "keyspaces.keyspace.details" this class="list-group-item keyspace-link"}}{{ name }}{{/link-to}}</li>
    {{/each}}
    </ul>
  </script>

  <script type="text/x-handlebars" data-template-name="details">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Header</th>
                  <th>Header</th>
                  <th>Header</th>
                  <th>Header</th>
                </tr>
              </thead>
              <tbody>
    {{#each model}}
                <tr>
                  <td>{{ name }}</td>
                  <td>Lorem</td>
                  <td>ipsum</td>
                  <td>dolor</td>
                  <td>sit</td>
                </tr>
    {{/each}}
              </tbody>
            </table>
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
