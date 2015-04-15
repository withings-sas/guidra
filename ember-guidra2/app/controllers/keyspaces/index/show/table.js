import Ember from 'ember';

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
		//var keyspace = that.get('keyspaceName');
		//var table = that.get('name');
		var query = that.get('cql_query');
		if( query ) {
			that.set('cql_query_loading', true);
			//var limit = that.get('cql_limit');
			//console.log(that.get('keyspaceName'));
			/*Ember.$.getJSON('http://yuki.lunasys.fr/results?table='+table+'&keyspace='+keyspace+'&limit=' + that.get("limit"), function(json) {
				that.set('extract', json);
			});*/
			//var query = "SELECT * FROM " + keyspace + "." + table + " WHERE " + where + " LIMIT " + limit;
			Ember.$.getJSON('http://yuki.lunasys.fr/query?q='+query, function(json) {
				that.set('cql_query_loading', false);
				if( json.rows && json.rows.length > 0 ) {
					that.set('extract', json);
				}
			});
		}
	}
  }
});
