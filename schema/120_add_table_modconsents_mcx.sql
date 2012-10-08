--
-- Table structure for table `modconsents`
--

CREATE TABLE IF NOT EXISTS `modconsents` (
  `mcid` decimal(16,0) NOT NULL,
  `friendmcid` decimal(16,0) NOT NULL,
  `since` int(11) NOT NULL,
  PRIMARY KEY  (`friendmcid`),
  KEY `mcid` (`mcid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
