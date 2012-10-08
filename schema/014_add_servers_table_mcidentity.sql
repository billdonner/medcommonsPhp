CREATE TABLE servers (
  id int(11) NOT NULL auto_increment,
  url varchar(128) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY url (url)
);
