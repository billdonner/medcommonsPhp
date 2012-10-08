--
-- Table structure for table `modfriends`
--

CREATE TABLE IF NOT EXISTS `modfriends` (
  `mcid` decimal(16,0) NOT NULL,
  `friendmcid` decimal(16,0) NOT NULL,
  `since` int(11) NOT NULL,
  PRIMARY KEY  (`friendmcid`),
  KEY `mcid` (`mcid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
