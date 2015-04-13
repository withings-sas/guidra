import Ember from 'ember';

export default Ember.Route.extend({

  renderTemplate: function() {
    this.render({
      outlet: 'books',
    });
  },

  model: function(params) {
    // using `fetch` instead of the usual `find` method
    // to always make a call to the API
    // regardless of the current store
    return this.store.getById('book', params.id);
  },

});

