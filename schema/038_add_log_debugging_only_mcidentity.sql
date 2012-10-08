-- 
-- Table structure for table `log`  ** this table is just for debugging
-- 

CREATE TABLE `log` (
  `content` varchar(255) NOT NULL default '',
  `time` time NOT NULL default '00:00:00',
  KEY `time` (`time`)
) TYPE=MyISAM;