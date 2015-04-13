import Ember from 'ember';

export default Ember.Route.extend({

  renderTemplate: function() {
    this.render({
      outlet: 'detail',
    });
  },

  model: function(params) {
    // using `fetch` instead of the usual `find` method
    // to always make a call to the API
    // regardless of the current store
    //return this.store.find('book', {keyspace_id: params.id}); //fetch('book', params.id);
    return this.store.getById('keyspace', params.id); //fetch('book', params.id);
  },

});
