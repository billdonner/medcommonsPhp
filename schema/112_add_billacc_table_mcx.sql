
--
-- Table structure for table `billacc`
--
CREATE TABLE IF NOT EXISTS `billacc` (
  `billingid` varchar(255) NOT NULL COMMENT 'arbitrary long id string',
  `accid` decimal(16,0) NOT NULL COMMENT 'medcommons id',
  `ProductCode` varchar(255) NOT NULL COMMENT 'amz product code for s3 storage product',
  `ActivationKey` varchar(255) NOT NULL COMMENT 'amz s3 activation key',
  PRIMARY KEY  (`accid`),
  KEY `billingid` (`billingid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='binds accounts together into one billing unit ';


--
-- Table structure for table `fpsipn`
--

CREATE TABLE IF NOT EXISTS `fpsipn` (
  `ord` int(11) NOT NULL auto_increment,
  `transactionid` varchar(255) NOT NULL,
  `referenceid` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `operation` varchar(255) NOT NULL,
  `paymentReason` varchar(255) NOT NULL,
  `transactionAmount` varchar(255) NOT NULL,
  `transactionDate` varchar(255) NOT NULL,
  `paymentMethod` varchar(255) NOT NULL,
  `recipientName` varchar(255) NOT NULL,
  `buyerName` varchar(255) NOT NULL,
  `recipientEmail` varchar(255) NOT NULL,
  `buyerEmail` varchar(255) NOT NULL,
  `time` int(255) NOT NULL,
  PRIMARY KEY  (`ord`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='precise log of amazon ipn returns' AUTO_INCREMENT=5 ;



-- --------------------------------------------------------

--
-- Table structure for table `prepay_counters`
--

CREATE TABLE IF NOT EXISTS `prepay_counters` (
  `billingid` varchar(255) NOT NULL COMMENT 'arbitrary string',
  `faxin` mediumint(9) NOT NULL COMMENT 'pos incoming fax page count',
  `dicom` mediumint(9) NOT NULL COMMENT 'dicom upload counter',
  `acc` mediumint(9) NOT NULL COMMENT 'account creation bounty',
  PRIMARY KEY  (`billingid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='shared credit buckets ';


