-- 
-- Table structure for table `rssheadlines` **new
-- 

CREATE TABLE `rssheadlines` (
  `id` int(11) NOT NULL auto_increment,
  `sourceid` smallint(6) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `description` tinytext NOT NULL,
  `pubDate` varchar(255) NOT NULL default '',
  `time` time NOT NULL default '00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='entires copied from rss feeds' AUTO_INCREMENT=5 ;