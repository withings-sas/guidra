import DS from "ember-data";

// define the User model
var Book = DS.Model.extend({
  name:  DS.attr('string'),
  keyspaceName:  DS.attr('string'),
  columnfamilyName:  DS.attr('string'),
  columnName:  DS.attr('string'),
  componentIndex:  DS.attr('string'),
  indexName:  DS.attr('string'),
  indexOptions:  DS.attr('string'),
  indexType:  DS.attr('string'),
  type:  DS.attr('string'),
  validator:  DS.attr('string')
});

// creates User fixtures
// this is what the FixtureAdapter uses as the API source
Book.reopenClass({
  FIXTURES: [
    {id: 1, name: "test book1"},
    {id: 2, name: "test book2"}
  ]
});

export default Book;
