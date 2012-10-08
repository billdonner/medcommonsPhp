

-- 
-- Table structure for table `personas`
-- 

DROP TABLE IF EXISTS `personas`;
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
  `email` varchar(255) NOT NULL default '',
  `exposeemail` tinyint(4) NOT NULL default '0',
  `inheritemail` tinyint(4) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `exposename` tinyint(4) NOT NULL default '0',
  `inheritname` tinyint(4) NOT NULL default '0',
  `address` varchar(255) NOT NULL default '',
  `exposeaddress` tinyint(4) NOT NULL default '0',
  `inheritaddress` tinyint(4) NOT NULL default '0',
  `dob` varchar(255) NOT NULL default '',
  `exposedob` tinyint(4) NOT NULL default '0',
  `inheritdob` tinyint(4) NOT NULL default '0',
  `sex` varchar(255) NOT NULL default '',
  `exposesex` tinyint(4) NOT NULL default '0',
  `inheritsex` tinyint(4) NOT NULL default '0',
  `ccrsectionconsents` varchar(255) NOT NULL default '',
  `qualitativeandmultichoice` varchar(255) NOT NULL default '',
  `distancecalcmin` varchar(255) NOT NULL default '',
  `nooldccrs` tinyint(4) NOT NULL default '0',
  `excluderefs` tinyint(4) NOT NULL default '0',
  `requiresms` tinyint(4) NOT NULL default '0',
  `promptmissing` tinyint(4) NOT NULL default '0',
  `mergeccr` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`accid`,`persona`)
) TYPE=MyISAM COMMENT='persona';
