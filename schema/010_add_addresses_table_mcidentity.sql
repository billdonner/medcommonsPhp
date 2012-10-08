-- 
-- Table structure for table `addresses`
-- 

CREATE TABLE `addresses` (
  `mcid` decimal(16,0) NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `address1` varchar(255) NOT NULL default '',
  `address2` varchar(255) default NULL,
  `city` varchar(64) NOT NULL default '',
  `state` varchar(8) NOT NULL default '',
  `postcode` varchar(16) NOT NULL default '',
  `country` char(2) NOT NULL default 'US',
  `telephone` varchar(32) default NULL,
  PRIMARY KEY  (`mcid`,`comment`)
) TYPE=MyISAM;
