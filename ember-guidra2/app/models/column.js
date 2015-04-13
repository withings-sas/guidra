import DS from "ember-data";

// define the User model
var Column = DS.Model.extend({
  columnName:  DS.attr('string'),
  keyspaceName:  DS.attr('string'),
  columnfamilyName:  DS.attr('string'),
  componentIndex:  DS.attr('string'),
  indexName:  DS.attr('string'),
  indexOptions:  DS.attr('string'),
  indexType:  DS.attr('string'),
  type:  DS.attr('string'),
  validator:  DS.attr('string')
});

export default Column;
