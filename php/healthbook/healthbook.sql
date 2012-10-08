-- MySQL dump 10.10
--
-- Host: localhost    Database: healthbook
-- ------------------------------------------------------
-- Server version	5.0.27

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `careteams`
--

DROP TABLE IF EXISTS `careteams`;
CREATE TABLE `careteams` (
  `fbid` decimal(16,0) NOT NULL,
  `giverfbid` decimal(16,0) NOT NULL,
  `giverrole` tinyint(4) NOT NULL default '0',
  `giverrights` varchar(255) NOT NULL,
  `lastinvite` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ctlinkid` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`ctlinkid`),
  UNIQUE KEY `fbid` (`fbid`,`giverfbid`),
  KEY `giverfbid` (`giverfbid`)
) ENGINE=MyISAM AUTO_INCREMENT=95 DEFAULT CHARSET=latin1 COMMENT='Links care givers to healthbook users';

--
-- Dumping data for table `careteams`
--

LOCK TABLES `careteams` WRITE;
/*!40000 ALTER TABLE `careteams` DISABLE KEYS */;
/*!40000 ALTER TABLE `careteams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carewalls`
--

DROP TABLE IF EXISTS `carewalls`;
CREATE TABLE `carewalls` (
  `id` int(11) NOT NULL auto_increment,
  `wallfbid` decimal(16,0) NOT NULL,
  `authorfbid` decimal(16,0) NOT NULL,
  `msg` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `wallfbid` (`wallfbid`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 COMMENT='everything ever written to any careteam wall';

--
-- Dumping data for table `carewalls`
--

LOCK TABLES `carewalls` WRITE;
/*!40000 ALTER TABLE `carewalls` DISABLE KEYS */;
/*!40000 ALTER TABLE `carewalls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fbtab`
--

DROP TABLE IF EXISTS `fbtab`;
CREATE TABLE `fbtab` (
  `fbid` decimal(16,0) NOT NULL,
  `mcid` decimal(16,0) NOT NULL,
  `applianceurl` varchar(255) NOT NULL,
  `targetfbid` decimal(16,0) NOT NULL,
  `targetmcid` decimal(16,0) NOT NULL,
  `groupid` decimal(16,0) default NULL,
  `gw` varchar(255) default NULL,
  `gw_modified_date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`fbid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fbtab`
--

LOCK TABLES `fbtab` WRITE;
/*!40000 ALTER TABLE `fbtab` DISABLE KEYS */;
/*!40000 ALTER TABLE `fbtab` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-12-07  4:22:10
