CREATE TABLE external_users (
  mcid decimal(16,0) NOT NULL default '0',
  provider_id int(11) NOT NULL default '0',
  username varchar(64) NOT NULL default '',
  PRIMARY KEY  (provider_id,username)
);
