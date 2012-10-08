--- Existing table does not auto_increment, relies on people inserting correct
--- values manually.  Begin at 100 which allows 100 practices to be created
--- with arbitrary ids and avoids conflicts with legacy data (hopefully
--- nobody has ids > 100 yet).
alter table practice auto_increment = 100;
--- Existing table has auto_increment, but we need to reserve some space for test
--- data so, start it at 100
alter table groupinstances auto_increment = 100;
