import Ember from 'ember';

export default Ember.ObjectController.extend({
  limit: 2,
  extract: false,

  actions: {
	test: function() {
		var that = this;
		var keyspace = that.get('keyspaceName');
		var table = that.get('name');
		//console.log(that.get('keyspaceName'));
		Ember.$.getJSON('http://yuki.lunasys.fr/results?table='+table+'&keyspace='+keyspace+'&limit=' + that.get("limit"), function(json) {
			that.set('extract', json);
		});
	}
  }
});
