alter table users add column photoUrl varchar(255);
CREATE TABLE affiliates (
  affiliatelogo varchar(255) NOT NULL default '',
  affiliateid int(11) NOT NULL default '0',
  affiliatename varchar(255) NOT NULL default ''
) COMMENT='one entry for each affiliate and logo';

CREATE TABLE ccdata (
  accid varchar(16) NOT NULL default '',
  nikname varchar(16) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  addr varchar(255) NOT NULL default '',
  city varchar(255) NOT NULL default '',
  state varchar(255) NOT NULL default '',
  zip varchar(16) NOT NULL default '',
  cardnum varchar(16) NOT NULL default '',
  expdate varchar(16) NOT NULL default ''
) COMMENT='stores all cc details for all users ';
