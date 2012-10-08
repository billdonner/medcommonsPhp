CREATE TABLE identity_providers (
  id int(11) NOT NULL auto_increment,
  source_id varchar(40) NOT NULL default '',
  name varchar(80) NOT NULL default '',
  logo varchar(64) default NULL,
  domain varchar(64) default NULL,
  logouturl varchar(128) default NULL,
  website varchar(64) default NULL,
  PRIMARY KEY  (id),
  KEY source_id (source_id)
);
