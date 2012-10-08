-- 
-- Table structure for table `rsssources` **new
-- 

CREATE TABLE `rsssources` (
  `id` smallint(6) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `copyright` varchar(255) NOT NULL default '',
  `language` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `webMaster` varchar(255) NOT NULL default '',
  `managingEditor` varchar(255) NOT NULL default '',
  `rssversion` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='details on rss sources' AUTO_INCREMENT=2 ;
