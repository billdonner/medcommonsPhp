CREATE TABLE `todir` ( 
  `xid` varchar(255) NOT NULL default '',
  `ctx` varchar(255) NOT NULL default '', 
  `alias` varchar(255) NOT NULL default '', 
  `contact` varchar(255) NOT NULL default '', 
  `time` int(11) NOT NULL default '0', 
  `accid` varchar(16) NOT NULL default '' ) 
COMMENT='holds mappings for to and replyto fields'; 
