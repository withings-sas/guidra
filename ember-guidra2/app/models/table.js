import DS from "ember-data";

// define the User model
var Table = DS.Model.extend({
  name:  DS.attr('string'),
  keyspaceName:  DS.attr('string'),
  columnfamilyName:  DS.attr('string'),
  bloomFilterFpChance:  DS.attr('string'),
  type:  DS.attr('string'),
  caching:  DS.attr('string'),
  cfId:  DS.attr('string'),
  columnAliases:  DS.attr('string'),
  comment:  DS.attr('string'),
  compactionStrategyClass:  DS.attr('string'),
  compactionStrategyOptions:  DS.attr('string'),
  comparator:  DS.attr('string'),
  compressionParameters:  DS.attr('string'),
  defaultTimeToLive:  DS.attr('string'),
  defaultValidator:  DS.attr('string'),
  droppedColumns:  DS.attr('string'),
  gcGraceSeconds:  DS.attr('string'),
  indexInterval:  DS.attr('string'),
  isDense:  DS.attr('string'),
  keyAliases:  DS.attr('string'),
  keyValidator:  DS.attr('string'),
  localReadRepairChance:  DS.attr('string'),
  maxCompactionThreshold:  DS.attr('string'),
  maxIndexInterval:  DS.attr('string'),
  memtableFlushPeriodInMs:  DS.attr('string'),
  minCompactionThreshold:  DS.attr('string'),
  minIndexInterval:  DS.attr('string'),
  readRepairChance:  DS.attr('string'),
  speculativeRetry:  DS.attr('string'),
  subcomparator:  DS.attr('string'),
  valueAlias:  DS.attr('string')
});

Table.reopenClass({
  FIXTURES: [
    {id: 1, keyspaceName: 'Steve', name: 'Jobs'},
    {id: 2, keyspaceName: 'Jony', name: 'Ive'}
  ]
});

export default Table;
