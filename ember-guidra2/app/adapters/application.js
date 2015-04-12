import DS from 'ember-data';
/*
export default DS.FixtureAdapter.extend({
  latency: 500
});
*/
export default DS.RESTAdapter.extend({
  host: "http://yuki.lunasys.fr"
});
