-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 07, 2005 at 09:56 PM
-- Server version: 3.23.58
-- PHP Version: 5.0.4
-- 
-- Database: `mcx`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `document`
-- 

CREATE TABLE `document` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guid` varchar(64) NOT NULL default '',
  `encrypted_key` varchar(64) default NULL,
  `creation_time` timestamp(14) NOT NULL,
  `rights_time` timestamp(14) NOT NULL,
  `encrypted_hash` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `document`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `document_location`
-- 

CREATE TABLE `document_location` (
  `document_id` int(10) unsigned NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  `node_node_id` bigint(20) NOT NULL default '0',
  `integrity_check` timestamp(14) NOT NULL,
  `integrity_status` int(10) unsigned default NULL,
  `encrypted_key` varchar(64) default NULL,
  `copy_number` int(10) unsigned default NULL,
  PRIMARY KEY  (`document_id`,`id`,`node_node_id`),
  KEY `DocumentLocation_FKIndex1` (`node_node_id`),
  KEY `DocumentLocation_FKIndex2` (`document_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `document_location`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `forensic_log`
-- 

CREATE TABLE `forensic_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `creation_time` timestamp(14) NOT NULL,
  `event_type` varchar(32) default NULL,
  `event_description` varchar(64) default NULL,
  `event_status` int(10) unsigned default NULL,
  `rights_id` int(10) unsigned default NULL,
  `rights_table` varchar(16) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `forensic_log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `group_node`
-- 

CREATE TABLE `group_node` (
  `node_node_id` bigint(20) NOT NULL default '0',
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_node_id`,`groups_group_number`),
  KEY `GroupNode_FKIndex1` (`node_node_id`),
  KEY `GroupNode_FKIndex2` (`groups_group_number`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `group_node`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `groups`
-- 

CREATE TABLE `groups` (
  `group_number` int(11) NOT NULL default '0',
  `name` varchar(64) default NULL,
  `location` varchar(64) default NULL,
  `group_type` varchar(32) default NULL,
  `admin_id` varchar(32) default NULL,
  `point_of_contact_id` varchar(32) default NULL,
  PRIMARY KEY  (`group_number`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `hipaa`
-- 

CREATE TABLE `hipaa` (
  `tracking_number` varchar(12) NOT NULL default '',
  `creation_time` timestamp(14) NOT NULL,
  `hpin` varchar(255) default NULL,
  `a1` varchar(32) default NULL,
  `a2` varchar(32) default NULL,
  `a3` varchar(32) default NULL,
  `s1` varchar(255) default NULL,
  `s2` varchar(255) default NULL,
  `s3` varchar(255) default NULL,
  `s4` varchar(255) default NULL,
  PRIMARY KEY  (`tracking_number`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `hipaa`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `hipaa_trace`
-- 

CREATE TABLE `hipaa_trace` (
  `tracking_number` varchar(12) NOT NULL default '',
  `creation_time` timestamp(14) NOT NULL,
  `hpin` varchar(255) default NULL,
  `a1` varchar(32) default NULL,
  `a2` varchar(32) default NULL,
  `a3` varchar(32) default NULL,
  `s1` varchar(255) default NULL,
  `s2` varchar(255) default NULL,
  `s3` varchar(255) default NULL,
  `s4` varchar(255) default NULL,
  PRIMARY KEY  (`tracking_number`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `hipaa_trace`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `inbox`
-- 

CREATE TABLE `inbox` (
  `inbox_id` int(10) unsigned NOT NULL auto_increment,
  `inbox_name` varchar(45) default NULL,
  `inbox_type` int(10) unsigned default NULL,
  `inbox_location` varchar(200) default NULL,
  PRIMARY KEY  (`inbox_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `inbox`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `inboxes`
-- 

CREATE TABLE `inboxes` (
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `inbox_id` int(10) unsigned NOT NULL default '0',
  `user_medcommons_user_id` varchar(32) NOT NULL default '',
  `descriptor` varchar(128) default NULL,
  `descriptor_type` int(10) unsigned default NULL,
  `authentication` int(10) unsigned default NULL,
  KEY `GroupInbox_FKIndex1` (`inbox_id`),
  KEY `Usernbox_FKIndex2` (`user_medcommons_user_id`),
  KEY `Inboxes_FKIndex3` (`groups_group_number`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `inboxes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `node`
-- 

CREATE TABLE `node` (
  `node_id` bigint(20) NOT NULL default '0',
  `admin_id` varchar(32) default NULL,
  `e_key` bigint(20) default NULL,
  `m_key` bigint(20) default NULL,
  `display_name` varchar(64) default NULL,
  `hostname` varchar(64) default NULL,
  `fixed_ip` int(11) default NULL,
  `node_type` int(11) default NULL,
  `creation_time` timestamp(14) NOT NULL,
  `logging_server` varchar(128) default NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `node`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `node_right`
-- 

CREATE TABLE `node_right` (
  `node_node_id` bigint(20) NOT NULL default '0',
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `rights` varchar(32) default NULL,
  PRIMARY KEY  (`node_node_id`,`groups_group_number`),
  KEY `NodeRights_FKIndex1` (`node_node_id`),
  KEY `NodeRights_FKIndex2` (`groups_group_number`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `node_right`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `rights`
-- 

CREATE TABLE `rights` (
  `rights_id` int(10) unsigned NOT NULL auto_increment,
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `user_medcommons_user_id` varchar(32) NOT NULL default '',
  `document_ID` int(10) unsigned NOT NULL default '0',
  `rights` varchar(32) NOT NULL default '',
  `creation_time` timestamp(14) NOT NULL,
  `expiration_time` timestamp(14) NOT NULL,
  `rights_time` timestamp(14) NOT NULL,
  `accepted_time` timestamp(14) NOT NULL,
  PRIMARY KEY  (`rights_id`),
  KEY `Rights_FKIndex3` (`groups_group_number`),
  KEY `Rights_FKIndex2` (`user_medcommons_user_id`),
  KEY `Right_FKIndex3` (`document_ID`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `rights`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tracking_number`
-- 

CREATE TABLE `tracking_number` (
  `tracking_number` varchar(64) NOT NULL default '',
  `rights_id` int(10) unsigned NOT NULL default '0',
  `encrypted_pin` varchar(64) default NULL,
  PRIMARY KEY  (`tracking_number`),
  KEY `TrackingNumber_FKIndex1` (`rights_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `tracking_number`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user`
-- 

CREATE TABLE `user` (
  `medcommons_user_id` varchar(32) NOT NULL default '',
  `telephone_number` varchar(64) default NULL,
  `email_address` varchar(64) default NULL,
  `credential` blob,
  `creation_time` timestamp(14) NOT NULL,
  `last_access_time` timestamp(14) NOT NULL,
  `ui_role` int(11) default NULL,
  `public_key` varchar(255) default NULL,
  `serial` varchar(32) default NULL,
  `hpass` varchar(255) default NULL,
  `gateway1` varchar(255) default NULL,
  `gateway2` varchar(255) default NULL,
  `identity_provider` varchar(255) default NULL,
  `cert_url` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `cert_checked` timestamp(14) NOT NULL,
  `wired_ipaddress` varchar(255) default NULL,
  `wired_useragent` varchar(255) default NULL,
  PRIMARY KEY  (`medcommons_user_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `user`
-- 

INSERT INTO `user` VALUES ('6992402135897401', NULL, 'blue', NULL, '20050802140437', '00000000000000', NULL, NULL, '0123456789ABCDEF', '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', 'http://gateway001.private.medcommons.net:9080/router', '', 'MedCommons Admin', '', '', 'blue', '20050802140437', '', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.8) Gecko/20050511 Firefox/1.0.4');
INSERT INTO `user` VALUES ('9193455144716247', NULL, 'billdonner@gmail.com', NULL, '20050802140500', '00000000000000', NULL, NULL, '0123456789ABCDEF', '8843d7f92416211de9ebb963ff4ce28125932878', 'http://gateway001.private.medcommons.net:9080/router', '', 'MedCommons Admin', '', '', 'billdonner@gmail.com', '20050802140500', '', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.8) Gecko/20050511 Firefox/1.0.4');
INSERT INTO `user` VALUES ('7149963268548360', NULL, 'onemctest@gmail.com', '', '20050802164840', '00000000000000', NULL, NULL, '2bc7967e70a2d52273ab2a77743d17a1', 'f95a85b6747454da03fafda1f78c9d5d197cd621', 'http://127.0.0.1:9080/router', '', 'VeriSign, Inc.', 'https://digitalid.verisign.com/cgi-bin/Xquery.exe?issuerSerial=67a773d0a80695c365cc281a5e7a983c&Template=retailCertByIssuer&form_file=../fdf/userQueryResult.fdf&qmCompileAlways=yes', 'Valid', 'One McTest', '20050802164532', '151.199.31.253', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6');

-- --------------------------------------------------------

-- 
-- Table structure for table `user_group`
-- 

CREATE TABLE `user_group` (
  `user_medcommons_user_id` varchar(32) NOT NULL default '',
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `user_role_with_group` varchar(32) NOT NULL default '',
  `added_by_id` varchar(32) default NULL,
  PRIMARY KEY  (`user_medcommons_user_id`,`groups_group_number`),
  KEY `UserGroup_FKIndex1` (`user_medcommons_user_id`),
  KEY `UserGroup_FKIndex2` (`groups_group_number`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `user_group`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `worklist_queue`
-- 

CREATE TABLE `worklist_queue` (
  `worklist_id` int(10) unsigned NOT NULL auto_increment,
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `user_medcommons_user_id` varchar(32) NOT NULL default '',
  `description` varchar(32) default NULL,
  PRIMARY KEY  (`worklist_id`),
  KEY `WorklistQueue_FKIndex1` (`user_medcommons_user_id`),
  KEY `WorklistQueue_FKIndex2` (`groups_group_number`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `worklist_queue`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `worklist_queue_item`
-- 

CREATE TABLE `worklist_queue_item` (
  `rights_id` int(10) unsigned NOT NULL default '0',
  `worklist_queue_worklist_id` int(10) unsigned NOT NULL default '0',
  `placed_in_queue` timestamp(14) NOT NULL,
  `order_number` int(10) unsigned default NULL,
  `priority` int(10) unsigned default NULL,
  PRIMARY KEY  (`rights_id`,`worklist_queue_worklist_id`),
  KEY `WorklistQueueItem_FKIndex1` (`worklist_queue_worklist_id`),
  KEY `WorklistQueueItem_FKIndex2` (`rights_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `worklist_queue_item`
-- 

