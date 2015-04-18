import DS from 'ember-data';
import config from '../config/environment';
/*
export default DS.FixtureAdapter.extend({
  latency: 500
});
*/
export default DS.RESTAdapter.extend({
  //host: "http://" + window.location.hostname
  host: config.APP.wsURL
  //host: "http://yuki.lunasys.fr"
});
