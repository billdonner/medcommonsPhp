CREATE TABLE `ccrlog` (
  `id` int(11) NOT NULL auto_increment,
  `accid` varchar(255) NOT NULL default '',
  `guid` varchar(64) NOT NULL default '0',
  `samplidp` varchar(255) NOT NULL,
  `status` varchar(12) NOT NULL default '',
  `date` timestamp(14) NOT NULL,
  `src` varchar(255) NOT NULL default '',
  `dest` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `accid` (`accid`)
) TYPE=MyISAM COMMENT='every touch of a document' AUTO_INCREMENT=55 ;