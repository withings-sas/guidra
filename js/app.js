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

App.Router.map(function() {
  //this.route('keyspaces');
  this.resource('keyspaces', function() {
    this.resource('keyspace', { path: ':name' }, function() {
      this.route('tables');
    });
  });
});

App.IndexRoute = Ember.Route.extend({
  beforeModel: function() {
    this.transitionTo('keyspaces');
  }
});

App.Keyspace = Ember.Object.extend({
  id: '',
  name: '',
  tables: []
});


App.Keyspace.reopenClass({
  extractTables: function(songsData, keyspace) {
    return songsData.map(function(table) {
      return App.Table.create({ id: table.id, name: table.name, keyspace: keyspace });
    });
  }
});

App.Table = Ember.Object.extend({
  id: '',
  name: '',
  keyspace: null
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

  actions: {
    createKeyspace: function() {
    }
  }
});

App.KeyspaceRoute = Ember.Route.extend({
  model: function(params) {
    return App.Adapter.ajax('/keyspace/' + params.name).then(function(data) {
      return App.Keyspace.createRecord(data);
    });
  }
});



App.KeyspaceTablesRoute = Ember.Route.extend({
  model: function(params) {
    ks = this.modelFor('keyspace');
    console.log(ks.tables);
    return ks.tables; //ks.get('tables');
  },
});

