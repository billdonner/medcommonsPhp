CREATE TABLE account_notifications (
  id int(12) unsigned NOT NULL auto_increment,
  mcid decimal(16,0),
  recipient varchar(60),
  status varchar(30),
  PRIMARY KEY  (id),
  KEY acct_notifications_key (recipient)
)
