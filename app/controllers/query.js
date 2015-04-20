import Ember from 'ember';
import config from '../config/environment';

export default Ember.ObjectController.extend({
  queryParams: {
    'q':{
      replace: true
    }
  },

  q: "",
  cql_query: "",
  cql_query_loading: false,
  extract: false,

  actions: {
	execute: function() {
		this.set('extract', false);
		var that = this;
		var query = that.get('q');
		console.log("Query:["+query+"]");
		if( query ) {
			that.set('cql_query_loading', true);
			Ember.$.getJSON(config.APP.wsURL + '/query?q='+query, function(json) {
				that.set('cql_query_loading', false);
				if( json.rows && json.rows.length > 0 ) {
					that.set('extract', json);
				}
			});
		}
	}
  }
});
