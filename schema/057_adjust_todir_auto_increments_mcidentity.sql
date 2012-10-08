--- Existing table has auto_increment, but we need to reserve some space for test
--- data so, start it at 100
alter table todir auto_increment = 100;
