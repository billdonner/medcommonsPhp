-- MySQL dump 10.11
--
-- Host: mysql.internal    Database: alertinfo
-- ------------------------------------------------------
-- Server version	5.0.45-log

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

USE alertinfo;

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
INSERT INTO `notifiers` VALUES
('apps.medcommons.net','6172303408@tmomail.net',3600,0,0),
('apps.medcommons.net','agropper@medcommons.net',3600,0,0),
('apps.medcommons.net','billdonner@medcommons.net',3600,0,0),
('apps.medcommons.net','boxer@medcommons.net',3600,0,0),
('apps.medcommons.net','sdoyle@medcommons.net',3600,0,0),
('apps.medcommons.net','ssadedin@medcommons.net',3600,0,0),
('ci.myhealthespace.com','6172303408@tmomail.net',3600,1220947704,10),
('ci.myhealthespace.com','agropper@medcommons.net',3600,1220947704,10),
('ci.myhealthespace.com','billdonner@medcommons.net',3600,1220947704,10),
('ci.myhealthespace.com','boxer@medcommons.net',3600,1220947704,10),
('ci.myhealthespace.com','sdoyle@medcommons.net',3600,1220947704,10),
('ci.myhealthespace.com','ssadedin@medcommons.net',3600,1220947704,10),
('ehgt.myhealthespace.com','6172303408@tmomail.net',3600,0,0),
('ehgt.myhealthespace.com','agropper@medcommons.net',3600,0,0),
('ehgt.myhealthespace.com','billdonner@medcommons.net',3600,0,0),
('ehgt.myhealthespace.com','boxer@medcommons.net',3600,0,0),
('ehgt.myhealthespace.com','sdoyle@medcommons.net',3600,0,0),
('ehgt.myhealthespace.com','ssadedin@medcommons.net',3600,0,0),
('notifier_test.medcommons.net','boxer@medcommons.net',86400,1221806792,46),
('notifier_test.medcommons.net','6172303408@tmomail.net',86400,1221806792,46),
('fb01.medcommons.net','6172303408@tmomail.net',3600,0,0),
('fb01.medcommons.net','agropper@medcommons.net',3600,0,0),
('fb01.medcommons.net','billdonner@medcommons.net',3600,0,0),
('fb01.medcommons.net','boxer@medcommons.net',3600,0,0),
('fb01.medcommons.net','sdoyle@medcommons.net',3600,0,0),
('fb01.medcommons.net','ssadedin@medcommons.net',3600,0,0),
('fb02.medcommons.net','6172303408@tmomail.net',3600,0,0),
('fb02.medcommons.net','agropper@medcommons.net',3600,0,0),
('fb02.medcommons.net','billdonner@medcommons.net',3600,0,0),
('fb02.medcommons.net','boxer@medcommons.net',3600,0,0),
('fb02.medcommons.net','sdoyle@medcommons.net',3600,0,0),
('fb02.medcommons.net','ssadedin@medcommons.net',3600,0,0),
('healthurl.medcommons.net','6172303408@tmomail.net',3600,1221796350,8),
('healthurl.medcommons.net','agropper@medcommons.net',3600,1221796350,8),
('healthurl.medcommons.net','billdonner@medcommons.net',3600,1221796350,8),
('healthurl.medcommons.net','boxer@medcommons.net',3600,1221796350,8),
('healthurl.medcommons.net','sdoyle@medcommons.net',3600,1221796350,8),
('healthurl.medcommons.net','ssadedin@medcommons.net',3600,1221796350,8),
('healthurl.myhealthespace.com','6172303408@tmomail.net',3600,0,0),
('healthurl.myhealthespace.com','agropper@medcommons.net',3600,0,0),
('healthurl.myhealthespace.com','billdonner@medcommons.net',3600,0,0),
('healthurl.myhealthespace.com','boxer@medcommons.net',3600,0,0),
('healthurl.myhealthespace.com','sdoyle@medcommons.net',3600,0,0),
('healthurl.myhealthespace.com','ssadedin@medcommons.net',3600,0,0),
('mcpurple06.myhealthespace.com','6172303408@tmomail.net',3600,1220947770,8),
('mcpurple06.myhealthespace.com','agropper@medcommons.net',3600,1220947770,8),
('mcpurple06.myhealthespace.com','billdonner@medcommons.net',3600,1220947770,8),
('mcpurple06.myhealthespace.com','boxer@medcommons.net',3600,1220947770,8),
('mcpurple06.myhealthespace.com','sdoyle@medcommons.net',3600,1220947770,8),
('mcpurple06.myhealthespace.com','ssadedin@medcommons.net',3600,1220947770,8),
('public.medcommons.net','6172303408@tmomail.net',3600,0,0),
('public.medcommons.net','agropper@medcommons.net',3600,0,0),
('public.medcommons.net','billdonner@medcommons.net',3600,0,0),
('public.medcommons.net','boxer@medcommons.net',3600,0,0),
('public.medcommons.net','sdoyle@medcommons.net',3600,0,0),
('public.medcommons.net','ssadedin@medcommons.net',3600,0,0),
('qatest.myhealthespace.com','6172303408@tmomail.net',3600,0,0),
('qatest.myhealthespace.com','agropper@medcommons.net',3600,0,0),
('qatest.myhealthespace.com','billdonner@medcommons.net',3600,0,0),
('qatest.myhealthespace.com','boxer@medcommons.net',3600,0,0),
('qatest.myhealthespace.com','sdoyle@medcommons.net',3600,0,0),
('qatest.myhealthespace.com','ssadedin@medcommons.net',3600,0,0),
('n0000.medcommons.net','6172303408@tmomail.net',3600,0,0),
('n0000.medcommons.net','agropper@medcommons.net',3600,0,0),
('n0000.medcommons.net','billdonner@medcommons.net',3600,0,0),
('n0000.medcommons.net','boxer@medcommons.net',3600,0,0),
('n0000.medcommons.net','sdoyle@medcommons.net',3600,0,0),
('n0000.medcommons.net','ssadedin@medcommons.net',3600,0,0),
('n0001.medcommons.net','6172303408@tmomail.net',3600,1221663680,1),
('n0001.medcommons.net','agropper@medcommons.net',3600,1221663680,1),
('n0001.medcommons.net','billdonner@medcommons.net',3600,1221663680,1),
('n0001.medcommons.net','boxer@medcommons.net',3600,1221663680,1),
('n0001.medcommons.net','sdoyle@medcommons.net',3600,1221663680,1),
('n0001.medcommons.net','ssadedin@medcommons.net',3600,1221663680,1),
('tenth.medcommons.net','6172303408@tmomail.net',3600,1221561121,13),
('tenth.medcommons.net','9178487175@cingularme.com',3600,1221561121,13),
('tenth.medcommons.net','agropper@medcommons.net',3600,1221561121,13),
('tenth.medcommons.net','billdonner@medcommons.net',3600,1221561121,13),
('tenth.medcommons.net','boxer@medcommons.net',3600,1221561121,13),
('tenth.medcommons.net','sdoyle@medcommons.net',3600,1221561121,13),
('tenth.medcommons.net','ssadedin@medcommons.net',3600,1221561121,13),
('testinstall.myhealthespace.com','6172303408@tmomail.net',3600,0,1),
('testinstall.myhealthespace.com','agropper@medcommons.net',3600,0,1),
('testinstall.myhealthespace.com','billdonner@medcommons.net',3600,0,1),
('testinstall.myhealthespace.com','boxer@medcommons.net',3600,0,1),
('testinstall.myhealthespace.com','sdoyle@medcommons.net',3600,0,1),
('testinstall.myhealthespace.com','ssadedin@medcommons.net',3600,0,1),
('www.myhealthespace.com','6172303408@tmomail.net',3600,1217122857,5),
('www.myhealthespace.com','agropper@medcommons.net',3600,1217122857,5),
('www.myhealthespace.com','billdonner@medcommons.net',3600,1217122857,5),
('www.myhealthespace.com','boxer@medcommons.net',3600,1217122857,5),
('www.myhealthespace.com','sdoyle@medcommons.net',3600,1217122857,5),
('www.myhealthespace.com','ssadedin@medcommons.net',3600,1217122857,5),
('www.medcommons.net','6172303408@tmomail.net',3600,1219836628,2),
('www.medcommons.net','9178487175@cingularme.com',3600,1219836628,2),
('www.medcommons.net','agropper@medcommons.net',3600,1219836628,2),
('www.medcommons.net','billdonner@medcommons.net',3600,1219836628,2),
('www.medcommons.net','sdoyle@medcommons.net',3600,1219836628,2),
('www.medcommons.net','ssadedin@medcommons.net',3600,1219836628,2);
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

-- Dump completed on 2008-09-20  6:00:45
