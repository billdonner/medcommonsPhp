-- 
-- Table structure for table `account_log`  ** new **
-- 

CREATE TABLE `account_log` (
  `datetime` timestamp(14) NOT NULL,
  `mcid` decimal(16,0) NOT NULL default '0',
  `username` varchar(64) default NULL,
  `provider_id` int(11) default NULL,
  `operation` varchar(16) default NULL
) TYPE=MyISAM;