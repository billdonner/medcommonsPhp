-- 
-- Table structure for table `personas`  ** this is new
-- 

CREATE TABLE `personas` (
  `accid` decimal(16,0) NOT NULL default '0',
  `persona` varchar(32) NOT NULL default '',
  `personanum` tinyint(4) NOT NULL default '0',
  `personagif` varchar(255) NOT NULL default '',
  `isactive` tinyint(4) NOT NULL default '0',
  `phone` varchar(255) NOT NULL default '',
  `exposephone` tinyint(4) NOT NULL default '0',
  `inheritphone` tinyint(4) NOT NULL default '0',
  `myid` varchar(255) NOT NULL default '',
  `exposemyid` tinyint(4) NOT NULL default '0',
  `inheritmyid` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`accid`,`persona`)
) TYPE=MyISAM COMMENT='persona';