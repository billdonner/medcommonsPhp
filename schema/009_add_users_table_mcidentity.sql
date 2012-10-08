
-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `mcid` decimal(16,0) NOT NULL default '0',
  `email` varchar(64) NOT NULL default '',
  `sha1` varchar(40) NOT NULL default '',
  `server_id` mediumint(9) NOT NULL default '0',
  `since` timestamp(14) NOT NULL,
  `first_name` varchar(32) NOT NULL default '',
  `last_name` varchar(32) NOT NULL default '',
  `mobile` varchar(64) default NULL,
  `smslogin` tinyint(4) default NULL,
  `updatetime` int(11) NOT NULL default '0',
  `ccrlogupdatetime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mcid`),
  KEY `email` (`email`)
) TYPE=MyISAM;
