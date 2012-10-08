-- MySQL dump 8.23
--
-- Host: localhost    Database: mcx
---------------------------------------------------------
-- Server version	3.23.58

--
-- Table structure for table `mcproperties`
--

CREATE TABLE mcproperties (
  property varchar(255) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  infourl varchar(255) NOT NULL default '',
  comment varchar(255) NOT NULL default ''
) TYPE=MyISAM COMMENT='MedCommons System Parameters';

--
-- Dumping data for table `mcproperties`
--


INSERT INTO mcproperties VALUES ('CreateCCRNodeID','138','','node where we make new ccrs, changes over time');

