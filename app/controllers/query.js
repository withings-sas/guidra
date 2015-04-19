import Ember from 'ember';

export default Ember.ObjectController.extend({
  queryParams: ['q'],

  q: "",
  cql_query: "",
  cql_query_loading: false,
  extract: false
});
