import Ember from 'ember';

export default Ember.Route.extend({
  renderTemplate: function() {
    this.render({
      outlet: 'tables',
    });
  },

  model: function(params) {
    // using `fetch` instead of the usual `find` method
    // to always make a call to the API
    // regardless of the current store
    return this.store.getById('table', params.id);
  },

  setupController: function(controller, table) {
    controller.set('model', table);
  }
});

