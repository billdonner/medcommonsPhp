-- 
-- Table structure for table `todir` ** very different in simons
-- 
DROP TABLE `todir`; 
CREATE TABLE `todir` (
  `id` int(11) NOT NULL auto_increment,
  `groupid` int(11) NOT NULL default '0',
  `xid` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `contactlist` varchar(255) NOT NULL default '',
  `sharedgroup` tinyint(4) NOT NULL default '0',
  `pinstate` tinyint(4) NOT NULL default '0',
  `accid` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='holds mappings for to and replyto fields' AUTO_INCREMENT=60 ;
