App = Ember.Application.create();

Ember.RSVP.configure('onerror', function(error) {
  if (error instanceof Error) {
    Ember.Logger.assert(false, error);
    Ember.Logger.error(error.stack);
  }
});

App.Adapter = {
  ajax: function(path, options) {
    var options = options || {};
    options.dataType = 'json';
    return ic.ajax.request('http://yuki.lunasys.fr' + path, options);
  }
}


/**
 * Router
 */
App.Router.map(function() {
  this.resource('keyspaces', function() {
    this.route('keyspace', { path: ':name' }, function() {
      this.route('details', { path: ':name' });
    });
  });
});


/**
 * Models
 */
App.Keyspace = Ember.Object.extend({
  id: '',
  name: ''
});

App.Table = Ember.Object.extend({
  id: '',
  name: ''
});


/**
 * Routes
 */
App.IndexRoute = Ember.Route.extend({
  beforeModel: function() {
    this.transitionTo('keyspaces');
  }
});


App.KeyspacesRoute = Ember.Route.extend({
  model: function() {
    return App.Adapter.ajax('/keyspaces').then(function(data) {
      //keyspaces = data.map(App.Keyspace.createRecord, App.Keyspace);
      return data; //Ember.RSVP.all(keyspaces);
    });
  },

  afterModel: function() {
    $(document).attr('title', 'All Keyspaces');
  },

  renderTemplate: function() {
    this.render({ outlet: 'keyspaces' });
  }
});


App.KeyspacesKeyspaceRoute = Ember.Route.extend({
  model: function(params) {
    /*ks = this.modelFor('keyspace');
    console.log(ks.tables);
    return ks.tables; //ks.get('tables');*/
    return App.Adapter.ajax('/tables/system').then(function(data) {
      return data; //.map(App.Table.createRecord, App.Table);
    });
  },

  renderTemplate: function() {
    this.render({ outlet: 'tables' });
  }
});


App.KeyspacesKeyspaceDetailsRoute = Ember.Route.extend({
  model: function(params) {
    console.log('heyy');
    return App.Adapter.ajax('/tables/system/' + params.name).then(function(data) {
      return data;
    });
  },

  renderTemplate: function() {
    this.render({ outlet: 'details' });
  }
});

