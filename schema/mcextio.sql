-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Oct 06, 2005 at 11:28 PM
-- Server version: 3.23.58
-- PHP Version: 5.0.4
-- 
-- Database: `mcextio`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `ccstatus`
-- 

CREATE TABLE `ccstatus` (
  `time` timestamp(14) NOT NULL,
  `authcode` varchar(255) NOT NULL default '',
  `avsdata` varchar(255) NOT NULL default '',
  `hostcode` varchar(255) NOT NULL default '',
  `pnref` varchar(255) NOT NULL default '',
  `respmsg` varchar(255) NOT NULL default '',
  `csmatch` varchar(255) NOT NULL default '',
  `custid` varchar(255) NOT NULL default '',
  `amount` varchar(255) NOT NULL default '',
  `user1` varchar(255) NOT NULL default '',
  `user2` varchar(255) NOT NULL default '',
  `user3` varchar(255) NOT NULL default '',
  `user4` varchar(255) NOT NULL default '',
  `user5` varchar(255) NOT NULL default '',
  `user6` varchar(255) NOT NULL default '',
  `user7` varchar(255) NOT NULL default '',
  `user8` varchar(255) NOT NULL default '',
  `user9` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default ''
) TYPE=MyISAM COMMENT='gets silent posts back from ecomm proider';

-- 
-- Dumping data for table `ccstatus`
-- 

INSERT INTO `ccstatus` VALUES ('20050726204736', '133107', 'YYY', '', 'VWYE0AD0A299', 'Approved', '', '12345678', '1.01', '4541475249842984', '', '', '', '', '', '', '', '/payservice/vssilent.php', 'S');
INSERT INTO `ccstatus` VALUES ('20050715185036', '711900', 'YYY', '', 'VDNE0ADD4DCB', 'Approved', '', '12345678', '1.01', '3715990983679640', '', '', '', '', '', '', '', '/payservice/vssilent.php', 'S');
INSERT INTO `ccstatus` VALUES ('20050726204559', '', 'XXN', '', 'VWYE0AD09F2C', 'Declined', '', '12345678', '1.01', '4541475249842984', '', '', '', '', '', '', '', '/payservice/vssilent.php', 'S');
INSERT INTO `ccstatus` VALUES ('20050712182940', '332777', 'YYY', '', 'VKME0A8F59C4', 'Approved', '', '12345678', '1.01', '3848264021094236', '', '', '', '', '', '', '', '/payservice/vssilent.php', 'S');
INSERT INTO `ccstatus` VALUES ('20050712122004', '099829', 'YYY', '', 'VKNE0A8BBD11', 'Approved', '', '12345678', '1.01', '6522548256705488', '', '', '', '', '', '', '', '/payservice/vssilent.php', 'S');
INSERT INTO `ccstatus` VALUES ('20050711153152', '010101', 'XXN', '', 'V54E0A51E852', 'Approved', '', '12345678', '1.01', '7526141196982493', '', '', '', '', '', '', '', '/payservice/vssilent.php', 'S');
INSERT INTO `ccstatus` VALUES ('20050711152428', '010101', 'XXN', '', 'V54E0A51E2E1', 'Approved', '', '12345678', '1.01', '2481946555976025', '', '', '', '', '', '', '', '/payservice/vssilent.php', 'S');

-- --------------------------------------------------------

-- 
-- Table structure for table `clicktracks`
-- 

CREATE TABLE `clicktracks` (
  `requesturi` varchar(255) NOT NULL default '',
  `time` timestamp(14) NOT NULL,
  `id` int(11) NOT NULL auto_increment,
  `referer` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='click table' AUTO_INCREMENT=1588 ;

-- 
-- Dumping data for table `clicktracks`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `cxpproblems`
-- 

CREATE TABLE `cxpproblems` (
  `id` bigint(20) NOT NULL auto_increment,
  `timestamp` timestamp(14) NOT NULL,
  `sender` varchar(255) NOT NULL default '',
  `version` varchar(255) NOT NULL default '',
  `problemdata` blob NOT NULL,
  `trackingnumber` varchar(255) NOT NULL default '',
  `pin` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `useragent` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='holds problem reports' AUTO_INCREMENT=44 ;

-- 
-- Dumping data for table `cxpproblems`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `downloaders`
-- 

CREATE TABLE `downloaders` (
  `email` varchar(255) NOT NULL default '',
  `time` timestamp(14) NOT NULL,
  `id` int(11) NOT NULL auto_increment,
  `remoteaddr` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='downloaders voluntary identities' AUTO_INCREMENT=70 ;

-- 
-- Dumping data for table `downloaders`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `emailstatus`
-- 

CREATE TABLE `emailstatus` (
  `status` varchar(255) NOT NULL default '',
  `time` timestamp(14) NOT NULL,
  `requesturi` varchar(255) NOT NULL default '',
  `sendermcid` varchar(255) NOT NULL default '',
  `rcvremail` varchar(255) NOT NULL default '',
  `template` varchar(255) NOT NULL default '',
  `arga` varchar(255) NOT NULL default '',
  `argb` varchar(255) NOT NULL default '',
  `argc` varchar(255) NOT NULL default '',
  `argd` varchar(255) NOT NULL default '',
  `arge` varchar(255) NOT NULL default '',
  `argf` varchar(255) NOT NULL default '',
  `argg` varchar(255) NOT NULL default '',
  `message` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`arga`)
) TYPE=MyISAM COMMENT='tracks email progress';

-- 
-- Dumping data for table `emailstatus`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `faxstatus`
-- 

CREATE TABLE `faxstatus` (
  `xmtTime` timestamp(14) NOT NULL,
  `xmtService` varchar(255) NOT NULL default '',
  `xmtTransmissionID` varchar(255) NOT NULL default '',
  `xmtDOCID` varchar(255) NOT NULL default '',
  `xmtStatusCode` varchar(255) NOT NULL default '',
  `xmtStatusDescription` varchar(255) NOT NULL default '',
  `xmtErrorLevel` varchar(255) NOT NULL default '',
  `xmtErrorMessage` varchar(255) NOT NULL default '',
  `faxnum` varchar(255) NOT NULL default '',
  `filespec` varchar(255) NOT NULL default '',
  `filetype` varchar(255) NOT NULL default '',
  `dispCompletionDate` varchar(255) NOT NULL default '',
  `dispFaxStatus` varchar(255) NOT NULL default '',
  `dispRecipientCSID` varchar(255) NOT NULL default '',
  `dispDuration` varchar(255) NOT NULL default '',
  `dispPagesSent` varchar(255) NOT NULL default '',
  `dispNumberOfRetries` varchar(255) NOT NULL default ''
) TYPE=MyISAM COMMENT='holds responses from fax service';

-- 
-- Dumping data for table `faxstatus`
-- 

