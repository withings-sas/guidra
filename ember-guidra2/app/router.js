import Ember from 'ember';
import config from './config/environment';

var Router = Ember.Router.extend({
  location: config.locationType
});

Router.map(function() {
  this.route('keyspaces', function() {
    this.route('index', { path: ''}, function() {
      this.route('show', { path: ':keyspaceid' }, function() {
        this.route('table', { path: ':id' });
      });
    });
  });
});

export default Router;
