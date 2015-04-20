import Ember from 'ember';
import config from '../../../../config/environment';

export default Ember.Route.extend({
  renderTemplate: function() {
    this.render({
      outlet: 'tables',
    });
  },

  model: function(params) {
    return this.store.getById('table', params.id);
  },

  setupController: function(controller, table) {
		controller.set('model', table);

		var that = controller;
		var keyspace_name = that.get('keyspaceName');
		var table_name = that.get('name');
		var limit = "5";
		var query = "SELECT * FROM " + keyspace_name + "." + table_name + ' LIMIT ' + limit;
		that.set('extract', false);
		that.set('cql_query', query);
		that.set('cql_query_loading', true);
		Ember.$.getJSON(config.APP.wsURL + '/query?q='+query, function(json) {
			that.set('cql_query_loading', false);
			if( json.rows && json.rows.length > 0 ) {
				that.set('extract', json);
			}
		});
  }
});

