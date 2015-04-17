import Ember from 'ember';

export default Ember.Route.extend({
  renderTemplate: function() {
    this.render({
      outlet: 'tables',
    });
  },

  model: function(params) {
    // using `fetch` instead of the usual `find` method
    // to always make a call to the API
    // regardless of the current store
    return this.store.getById('table', params.id);
  },

  setupController: function(controller, table) {
		controller.set('model', table);

		var that = controller;
		var keyspace_name = that.get('keyspaceName');
		var table_name = that.get('name');
		var limit = "5";
		//console.log(that.get('keyspaceName'));
		var query = "SELECT * FROM " + keyspace_name + "." + table_name + ' LIMIT ' + limit;
		that.set('extract', false);
		that.set('cql_query', query);
		that.set('cql_query_loading', true);
		Ember.$.getJSON('http://yuki.lunasys.fr/query/' + keyspace_name + "/" + table_name + '?q='+query, function(json) {
			that.set('cql_query_loading', false);
			if( json.rows && json.rows.length > 0 ) {
				that.set('extract', json);
				/*var columns_names = [];
				json.columns.forEach(function(column_name) {
					columns_names.push(keyspace_name+":"+table_name+":"+column_name);
				});
				json.columns_record = [];
				that.store.find('column', {'ids': columns_names}).then(function(columns) {
					console.log(columns);
					json.columns_record = columns;
					that.set('extract', json);
				});*/
			}
		});
  }
});

