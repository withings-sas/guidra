import Ember from 'ember';

export default Ember.ObjectController.extend({
  cql_query: "",
  cql_query_loading: false,
  extract: false,

  attributes: function() {
    var tab = this.get('content');
    var attributes = Ember.get(tab, 'data');
    var array = Ember.$.map(attributes, function(value, index) {
      if( index !== "columns" ) {
        return [{"key": index, "value": value}];
      }
    });
    return array;
  }.property(),

  actions: {
	execute: function() {
      this.transitionTo('query', {'queryParams': {'q': this.cql_query}});
	}
  }
});
