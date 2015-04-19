import Ember from 'ember';
import config from '../config/environment';

export default Ember.Route.extend({
	setupController: function(controller) {
		var that = controller;
		var query = that.get('q');
		console.log("QUERY:["+query+"]");
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

