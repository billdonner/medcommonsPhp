DROP TABLE IF EXISTS `modcoupons`;
CREATE TABLE `modcoupons` (
  `couponum` int(11) NOT NULL auto_increment,
  `svcnum` mediumint(9) NOT NULL,
  `patientname` varchar(255) NOT NULL,
  `patientemail` varchar(255) NOT NULL,
  `addinfo` varchar(255) NOT NULL,
  `patientprice` int(11) NOT NULL,
  `expirationdate` varchar(255) NOT NULL,
  `hurl` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `mcid` decimal(16,0) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `accesstoken` varchar(255) NOT NULL,
  `paytype` varchar(16) NOT NULL,
  `paytid` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`couponum`),
  KEY `svcnum` (`svcnum`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=latin1 COMMENT='every coupon/account ever printed';

DROP TABLE IF EXISTS `modservices`;
CREATE TABLE `modservices` (
  `svcnum` mediumint(9) NOT NULL auto_increment,
  `accid` decimal(16,0) NOT NULL,
  `servicename` varchar(255) NOT NULL,
  `servicedescription` varchar(255) NOT NULL,
  `serviceemail` varchar(255) NOT NULL,
  `supportphone` varchar(255) NOT NULL,
  `duration` tinyint(4) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`svcnum`),
  UNIQUE KEY `accid` (`accid`,`servicename`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1 COMMENT='svcs defined by mod providers';

