import Ember from 'ember';
import config from '../../../../config/environment';

export default Ember.ObjectController.extend({
  //cql_where: "",
  //cql_limit: 50,
  cql_query: "",
  cql_query_loading: false,
  extract: false,

  actions: {
	test: function() {
		this.set('extract', false);
		var that = this;
		var keyspace_name = that.get('keyspaceName');
		var table_name = that.get('name');
		var query = that.get('cql_query');
		if( query ) {
			that.set('cql_query_loading', true);
			Ember.$.getJSON(config.APP.wsURL + '/query/' + keyspace_name + "/" + table_name + '?q='+query, function(json) {
				that.set('cql_query_loading', false);
				if( json.rows && json.rows.length > 0 ) {
					that.set('extract', json);
				}
			});
		}
	}
  }
});
