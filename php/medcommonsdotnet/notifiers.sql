-- MySQL dump 10.11
--
-- Host: mysql.internal    Database: alertinfo
-- ------------------------------------------------------
-- Server version	5.0.45

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
-- Table structure for table `notifiers`
--

DROP TABLE IF EXISTS `notifiers`;
CREATE TABLE `notifiers` (
  `server` varchar(255) NOT NULL COMMENT 'checked once per cycle',
  `endpoint` varchar(255) NOT NULL COMMENT 'email address or phone number or whatever',
  `mintime` int(11) NOT NULL COMMENT 'how long to wait before sending next alert re bad server',
  `lasttime` int(11) NOT NULL COMMENT 'time of last alert for this user',
  `noticecount` int(11) NOT NULL COMMENT 'times this particular endpoint has been notified',
  PRIMARY KEY  (`server`,`endpoint`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='maps who cares about what';

--
-- Dumping data for table `notifiers`
--

LOCK TABLES `notifiers` WRITE;
/*!40000 ALTER TABLE `notifiers` DISABLE KEYS */;
INSERT INTO `notifiers` VALUES ('apps.medcommons.net','6172303408@tmomail.net',3600,1229472604,6),
('apps.medcommons.net','agropper@medcommons.net',3600,1229472604,6),
('apps.medcommons.net','billdonner@medcommons.net',3600,1229472604,6),
('apps.medcommons.net','boxer@medcommons.net',3600,1229472604,6),
('apps.medcommons.net','ssadedin@medcommons.net',3600,1229472604,6),
('ci.myhealthespace.com','6172303408@tmomail.net',3600,1231695686,38),
('ci.myhealthespace.com','agropper@medcommons.net',3600,1231695686,38),
('ci.myhealthespace.com','billdonner@medcommons.net',3600,1231695686,38),
('ci.myhealthespace.com','boxer@medcommons.net',3600,1231695686,38),
('ci.myhealthespace.com','ssadedin@medcommons.net',3600,1231695686,38),
('notifier_test.medcommons.net','boxer@medcommons.net',86400,1231864676,162),
('notifier_test.medcommons.net','6172303408@tmomail.net',86400,1231864676,162),
('fb01.medcommons.net','6172303408@tmomail.net',3600,1231524618,6),
('fb01.medcommons.net','agropper@medcommons.net',3600,1231524618,6),
('fb01.medcommons.net','billdonner@medcommons.net',3600,1231524618,6),
('fb01.medcommons.net','boxer@medcommons.net',3600,1231524618,6),
('fb01.medcommons.net','ssadedin@medcommons.net',3600,1231524618,6),
('fb02.medcommons.net','6172303408@tmomail.net',3600,1231524629,3),
('fb02.medcommons.net','agropper@medcommons.net',3600,1231524629,3),
('fb02.medcommons.net','billdonner@medcommons.net',3600,1231524629,3),
('fb02.medcommons.net','boxer@medcommons.net',3600,1231524629,3),
('fb02.medcommons.net','ssadedin@medcommons.net',3600,1231524629,3),
('healthurl.medcommons.net','6172303408@tmomail.net',3600,1231709635,24),
('healthurl.medcommons.net','agropper@medcommons.net',3600,1231709635,24),
('healthurl.medcommons.net','billdonner@medcommons.net',3600,1231709635,24),
('healthurl.medcommons.net','boxer@medcommons.net',3600,1231709635,24),
('healthurl.medcommons.net','ssadedin@medcommons.net',3600,1231709635,24),
('healthurl.myhealthespace.com','6172303408@tmomail.net',3600,1231524651,2),
('healthurl.myhealthespace.com','agropper@medcommons.net',3600,1231524651,2),
('healthurl.myhealthespace.com','billdonner@medcommons.net',3600,1231524651,2),
('healthurl.myhealthespace.com','boxer@medcommons.net',3600,1231524651,2),
('healthurl.myhealthespace.com','ssadedin@medcommons.net',3600,1231524651,2),
('mcpurple06.myhealthespace.com','6172303408@tmomail.net',3600,1231695748,34),
('mcpurple06.myhealthespace.com','agropper@medcommons.net',3600,1231695748,34),
('mcpurple06.myhealthespace.com','billdonner@medcommons.net',3600,1231695748,34),
('mcpurple06.myhealthespace.com','boxer@medcommons.net',3600,1231695748,34),
('mcpurple06.myhealthespace.com','ssadedin@medcommons.net',3600,1231695748,34),
('public.medcommons.net','6172303408@tmomail.net',3600,1231744315,8),
('public.medcommons.net','agropper@medcommons.net',3600,1231744315,8),
('public.medcommons.net','billdonner@medcommons.net',3600,1231744315,8),
('public.medcommons.net','boxer@medcommons.net',3600,1231744315,8),
('public.medcommons.net','ssadedin@medcommons.net',3600,1231744315,8),
('qatest.myhealthespace.com','6172303408@tmomail.net',3600,1223011557,1),
('qatest.myhealthespace.com','agropper@medcommons.net',3600,1223011557,1),
('qatest.myhealthespace.com','billdonner@medcommons.net',3600,1223011557,1),
('qatest.myhealthespace.com','boxer@medcommons.net',3600,1223011557,1),
('qatest.myhealthespace.com','ssadedin@medcommons.net',3600,1223011557,1),
('n0000.medcommons.net','6172303408@tmomail.net',3600,1231706635,87),
('n0000.medcommons.net','agropper@medcommons.net',3600,1231706635,87),
('n0000.medcommons.net','billdonner@medcommons.net',3600,1231706635,87),
('n0000.medcommons.net','boxer@medcommons.net',3600,1231706635,87),
('n0000.medcommons.net','ssadedin@medcommons.net',3600,1231706635,87),
('n0001.medcommons.net','6172303408@tmomail.net',3600,1231744854,14),
('n0001.medcommons.net','agropper@medcommons.net',3600,1231744854,14),
('n0001.medcommons.net','billdonner@medcommons.net',3600,1231744854,14),
('n0001.medcommons.net','boxer@medcommons.net',3600,1231744854,14),
('n0001.medcommons.net','ssadedin@medcommons.net',3600,1231744854,14),
('tenth.medcommons.net','6172303408@tmomail.net',3600,1231744615,43),
('tenth.medcommons.net','9178487175@cingularme.com',3600,1231744615,43),
('tenth.medcommons.net','agropper@medcommons.net',3600,1231744615,43),
('tenth.medcommons.net','billdonner@medcommons.net',3600,1231744615,43),
('tenth.medcommons.net','boxer@medcommons.net',3600,1231744615,43),
('tenth.medcommons.net','ssadedin@medcommons.net',3600,1231744615,43),
('www.myhealthespace.com','6172303408@tmomail.net',3600,1226010451,7),
('www.myhealthespace.com','agropper@medcommons.net',3600,1226010451,7),
('www.myhealthespace.com','billdonner@medcommons.net',3600,1226010451,7),
('www.myhealthespace.com','boxer@medcommons.net',3600,1226010451,7),
('www.myhealthespace.com','ssadedin@medcommons.net',3600,1226010451,7),
('www.medcommons.net','6172303408@tmomail.net',3600,1227738361,6),
('www.medcommons.net','9178487175@cingularme.com',3600,1227738361,6),
('www.medcommons.net','agropper@medcommons.net',3600,1227738361,6),
('www.medcommons.net','billdonner@medcommons.net',3600,1227738361,6),
('www.medcommons.net','boxer@medcommons.net',3600,1226010451,6),
('www.medcommons.net','ssadedin@medcommons.net',3600,1227738361,6),
('simtrak.medcommons.net','6172303408@tmomail.net',3600,1227738361,0),
('simtrak.medcommons.net','9178487175@cingularme.com',3600,1227738361,0),
('simtrak.medcommons.net','agropper@medcommons.net',3600,1227738361,0),
('simtrak.medcommons.net','billdonner@medcommons.net',3600,1227738361,0),
('simtrak.medcommons.net','boxer@medcommons.net',3600,1226010451,0),
('simtrak.medcommons.net','ssadedin@medcommons.net',3600,1227738361,0);
/*!40000 ALTER TABLE `notifiers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-01-14  5:12:37
