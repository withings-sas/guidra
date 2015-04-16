import Ember from 'ember';

export default Ember.Route.extend({
  renderTemplate: function() {
    this.render({
      outlet: 'tables',
    });
  },

  actions: {
	save: function() {
      var table_name = this.controller.get("table_name");
      var table_pkey = this.controller.get("table_pkey");
	  console.log("**** SAVE [" + table_name + "] pkey:[" + table_pkey + "] ****");
	  var newTable = this.store.createRecord("table", {name: table_name});

      var cols = [];
      cols.push({
		  'name': this.controller.get("col_name"),
		  'type': this.controller.get("col_type")
	  });
	  //console.log(cols);
	  for( var i in cols ) {
		var col = cols[i];
		if( col.type == "" ) {
		  break;
		}
	    console.log("**** col:[" + col.name + "] ****");
	    var newColumn = this.store.createRecord("column", {id: col.name+":"+col.type, columnName: col.name});
	    console.log("**** col object:[" + newColumn.id + "] ****");
	    newTable.get('columns').pushObject(newColumn);
	  }
	  //newColumn.save().then(function() {
	  newTable.save();
	  //});
	}
  }
});
