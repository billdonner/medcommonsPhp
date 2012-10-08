SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT;
SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS;
SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION;
SET CHARACTER_SET_CLIENT = utf8;
SET NAMES utf8;
SET @OLD_TIME_ZONE=@@TIME_ZONE;
SET TIME_ZONE='+00:00';
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0;
CREATE TABLE `account_log` (
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `mcid` decimal(16,0) NOT NULL default '0',
  `username` varchar(64) default NULL,
  `provider_id` int(11) default NULL,
  `operation` varchar(16) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `account_notifications` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `mcid` decimal(16,0) default NULL,
  `recipient` varchar(60) default NULL,
  `status` varchar(30) default NULL,
  PRIMARY KEY  (`id`),
  KEY `acct_notifications_key` (`recipient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `account_rls` (
  `ar_accid` varchar(32) NOT NULL default '',
  `ar_rls_url` text NOT NULL,
  PRIMARY KEY  (`ar_accid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='registries to which account update notifications will be sen';
CREATE TABLE `addresses` (
  `mcid` decimal(16,0) NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `address1` varchar(255) NOT NULL default '',
  `address2` varchar(255) default NULL,
  `city` varchar(64) NOT NULL default '',
  `state` varchar(8) NOT NULL default '',
  `postcode` varchar(16) NOT NULL default '',
  `country` char(2) NOT NULL default 'US',
  `telephone` varchar(32) default NULL,
  PRIMARY KEY  (`mcid`,`comment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `affiliates` (
  `affiliatelogo` varchar(255) NOT NULL default '',
  `affiliateid` int(11) NOT NULL default '0',
  `affiliatename` varchar(255) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='one entry for each affiliate and logo';
CREATE TABLE `appeventlog` (
  `accid` varchar(16) NOT NULL default '',
  `appserviceid` varchar(32) NOT NULL default '',
  `eventname` varchar(255) NOT NULL default '',
  `param1` varchar(255) NOT NULL default '',
  `time` int(11) NOT NULL default '0',
  `chargeclass` varchar(255) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='log of billable events';
CREATE TABLE `appservicechargeclasses` (
  `appserviceid` varchar(32) NOT NULL default '',
  `chargeclass` varchar(255) NOT NULL default '',
  `permonth` int(11) NOT NULL default '0',
  `perclick` int(11) NOT NULL default '0',
  `perxmtgb` int(11) NOT NULL default '0',
  `perrcvgb` int(11) NOT NULL default '0',
  `setup` int(11) NOT NULL default '0',
  `perstoredgb` int(11) NOT NULL default '0',
  PRIMARY KEY  (`appserviceid`,`chargeclass`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='pricing for this service';
CREATE TABLE `appservicecontracts` (
  `accid` varchar(16) NOT NULL default '',
  `appserviceid` varchar(32) NOT NULL default '',
  `time` time default NULL,
  PRIMARY KEY  (`accid`,`appserviceid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `appservicedependencies` (
  `appserviceid` varchar(32) NOT NULL default '',
  `dependson` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`appserviceid`,`dependson`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='details appservice loading/unloading dependencies';
CREATE TABLE `appservices` (
  `name` varchar(255) NOT NULL default '',
  `serviceurl` varchar(255) NOT NULL default '',
  `publisher` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `appserviceid` varchar(32) NOT NULL default '',
  `createurl` varchar(255) NOT NULL default '',
  `removeurl` varchar(255) NOT NULL default '',
  `viewurl` varchar(255) NOT NULL default '',
  `builtin` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`appserviceid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='3rd party apps and services ';
CREATE TABLE `auth_group` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `auth_group_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `group_id` (`group_id`,`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `auth_message` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `auth_message_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `auth_permission` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `codename` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `content_type_id` (`content_type_id`,`codename`),
  KEY `auth_permission_content_type_id` (`content_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `auth_user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(75) NOT NULL,
  `password` varchar(128) NOT NULL,
  `is_staff` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_superuser` tinyint(1) NOT NULL,
  `last_login` datetime NOT NULL,
  `date_joined` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `auth_user_groups` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `auth_user_user_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`,`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `authentication_token` (
  `at_id` int(10) unsigned NOT NULL auto_increment,
  `at_token` varchar(40) default NULL,
  `at_account_id` varchar(32) default NULL,
  `at_create_date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `at_es_id` int(9) default NULL,
  `at_parent_at_id` int(10) unsigned default NULL,
  `at_secret` char(40) default NULL,
  `at_priority` varchar(30) default NULL,
  PRIMARY KEY  (`at_id`),
  KEY `idx_at_token` (`at_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `ccdata` (
  `accid` varchar(16) NOT NULL default '',
  `nikname` varchar(16) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `addr` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `state` varchar(255) NOT NULL default '',
  `zip` varchar(16) NOT NULL default '',
  `cardnum` varchar(16) NOT NULL default '',
  `expdate` varchar(16) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='stores all cc details for all users ';
CREATE TABLE `ccrevents` (
  `PatientGivenName` varchar(64) NOT NULL default '',
  `PatientFamilyName` varchar(64) NOT NULL default '',
  `PatientIdentifier` varchar(64) NOT NULL default '',
  `PatientIdentifierSource` varchar(64) NOT NULL default '',
  `Guid` varchar(64) NOT NULL default '',
  `Purpose` varchar(64) NOT NULL default '',
  `SenderProviderId` varchar(64) NOT NULL default '',
  `ReceiverProviderId` varchar(64) NOT NULL default '',
  `DOB` varchar(64) NOT NULL default '',
  `CXPServerURL` varchar(255) NOT NULL default '',
  `CXPServerVendor` varchar(255) NOT NULL default '',
  `ViewerURL` varchar(255) NOT NULL default '',
  `Comment` varchar(255) NOT NULL default '',
  `CreationDateTime` bigint(20) NOT NULL default '0',
  `ConfirmationCode` varchar(64) NOT NULL default '',
  `RegistrySecret` varchar(64) NOT NULL default '',
  `PatientSex` varchar(64) default NULL,
  `PatientAge` varchar(64) default NULL,
  `Status` varchar(30) default NULL,
  KEY `ccrevents_status_idx` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `ccrlog` (
  `id` int(11) NOT NULL auto_increment,
  `accid` decimal(16,0) NOT NULL default '0',
  `idp` varchar(255) NOT NULL default '',
  `guid` varchar(64) NOT NULL default '0',
  `status` varchar(12) NOT NULL default '',
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `src` varchar(255) NOT NULL default '',
  `dest` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `einfo` text,
  `tracking` varchar(12) default NULL,
  `merge_status` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  KEY `accid` (`accid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='every touch of a document';
CREATE TABLE `cover` (
  `cover_id` int(10) unsigned NOT NULL auto_increment,
  `cover_account_id` varchar(20) NOT NULL,
  `cover_notification` varchar(120) default NULL,
  `cover_encrypted_pin` varchar(64) default NULL,
  `cover_provider_code` varchar(30) default NULL,
  `cover_pin` varchar(12) default NULL,
  `cover_title` varchar(60) default NULL,
  `cover_note` varchar(255) default NULL,
  PRIMARY KEY  (`cover_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `django_admin_log` (
  `id` int(11) NOT NULL auto_increment,
  `action_time` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_type_id` int(11) default NULL,
  `object_id` longtext,
  `object_repr` varchar(200) NOT NULL,
  `action_flag` smallint(5) unsigned NOT NULL,
  `change_message` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `django_admin_log_user_id` (`user_id`),
  KEY `django_admin_log_content_type_id` (`content_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `django_content_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `app_label` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `app_label` (`app_label`,`model`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime NOT NULL,
  PRIMARY KEY  (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL auto_increment,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `document` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guid` varchar(64) NOT NULL default '',
  `creation_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `rights_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `encrypted_hash` varchar(64) default NULL,
  `storage_account_id` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  KEY `document_guid_idx` (`guid`),
  KEY `document_storage_idx` (`storage_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `document_location` (
  `document_id` int(10) unsigned NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  `node_node_id` bigint(20) NOT NULL default '0',
  `integrity_check` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `integrity_status` int(10) unsigned default NULL,
  `encrypted_key` varchar(64) default NULL,
  `copy_number` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `document_location_node_idx` (`document_id`,`node_node_id`),
  UNIQUE KEY `document_id` (`document_id`,`id`,`node_node_id`),
  KEY `DocumentLocation_FKIndex1` (`node_node_id`),
  KEY `DocumentLocation_FKIndex2` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `document_type` (
  `dt_id` int(10) unsigned NOT NULL auto_increment,
  `dt_account_id` varchar(20) NOT NULL,
  `dt_type` varchar(30) NOT NULL,
  `dt_tracking_number` varchar(20) NOT NULL,
  `dt_privacy_level` varchar(30) NOT NULL,
  `dt_guid` varchar(40) default NULL,
  `dt_create_date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `dt_comment` varchar(255) default NULL,
  `dt_notification_status` varchar(30) default NULL,
  PRIMARY KEY  (`dt_id`),
  KEY `idx_dt_account_id` (`dt_account_id`),
  KEY `idx_dt_tracking_number` (`dt_tracking_number`),
  KEY `idx_dt_type` (`dt_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `external_application` (
  `ea_id` int(9) NOT NULL auto_increment,
  `ea_key` varchar(255) default NULL,
  `ea_code` varchar(30) default NULL,
  `ea_name` varchar(255) default NULL,
  `ea_active_status` varchar(30) default NULL,
  `ea_ip_address` varchar(60) default NULL,
  `ea_create_date_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ea_secret` varchar(40) default NULL,
  `ea_web_site_url` varchar(255) default NULL,
  `ea_contact_email` varchar(255) default NULL,
  PRIMARY KEY  (`ea_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `external_share` (
  `es_id` int(9) NOT NULL auto_increment,
  `es_identity` text,
  `es_identity_type` varchar(30) default NULL,
  `es_first_name` varchar(60) default NULL,
  `es_last_name` varchar(60) default NULL,
  `es_create_date_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`es_id`),
  KEY `idx_external_share_identity` (`es_identity`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='rights granted to non-medcommons accounts eg. open id';
CREATE TABLE `external_users` (
  `mcid` decimal(16,0) NOT NULL default '0',
  `provider_id` int(11) NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`provider_id`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `forensic_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `creation_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `event_type` varchar(32) default NULL,
  `event_description` varchar(64) default NULL,
  `event_status` int(10) unsigned default NULL,
  `rights_id` int(10) unsigned default NULL,
  `rights_table` varchar(16) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `group_node` (
  `node_node_id` bigint(20) NOT NULL default '0',
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_node_id`,`groups_group_number`),
  KEY `GroupNode_FKIndex1` (`node_node_id`),
  KEY `GroupNode_FKIndex2` (`groups_group_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `groupadmins` (
  `groupinstanceid` int(11) NOT NULL default '0',
  `adminaccid` decimal(16,0) NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`groupinstanceid`,`adminaccid`),
  KEY `memberaccid` (`adminaccid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='every member of any group';
CREATE TABLE `groupinstances` (
  `groupinstanceid` int(11) NOT NULL auto_increment,
  `grouptypeid` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `groupLogo` varchar(255) NOT NULL default '',
  `adminUrl` varchar(255) NOT NULL default '',
  `memberUrl` varchar(255) NOT NULL default '',
  `parentid` int(11) NOT NULL default '0',
  `accid` varchar(32) default NULL,
  `createdatetime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `worklist_limit` int(11) default NULL,
  PRIMARY KEY  (`groupinstanceid`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='one row for each practice group or sig group...';
CREATE TABLE `groupmembers` (
  `groupinstanceid` int(11) NOT NULL default '0',
  `memberaccid` decimal(16,0) NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`groupinstanceid`,`memberaccid`),
  KEY `adminaccid` (`memberaccid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='every member of any group';
CREATE TABLE `groupproperties` (
  `groupinstanceid` int(11) NOT NULL default '0',
  `property` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`groupinstanceid`,`property`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='properties for specific group types';
CREATE TABLE `groups` (
  `group_number` int(11) NOT NULL default '0',
  `name` varchar(64) default NULL,
  `location` varchar(64) default NULL,
  `group_type` varchar(32) default NULL,
  `admin_id` varchar(32) default NULL,
  `point_of_contact_id` varchar(32) default NULL,
  PRIMARY KEY  (`group_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `grouptypes` (
  `grouptypeid` int(11) NOT NULL default '0',
  `name` varchar(32) NOT NULL default '',
  `infoUrl` varchar(255) NOT NULL default '',
  `rulesUrl` varchar(255) NOT NULL default '',
  `supportPageUrl` varchar(255) NOT NULL default '',
  `internalgroup` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`grouptypeid`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='one row for each type of MedCommons Group';
CREATE TABLE `hipaa` (
  `tracking_number` varchar(12) NOT NULL default '',
  `creation_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `hpin` varchar(255) default NULL,
  `a1` varchar(32) default NULL,
  `a2` varchar(32) default NULL,
  `a3` varchar(32) default NULL,
  `s1` varchar(255) default NULL,
  `s2` varchar(255) default NULL,
  `s3` varchar(255) default NULL,
  `s4` varchar(255) default NULL,
  PRIMARY KEY  (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `hipaa_trace` (
  `tracking_number` varchar(12) NOT NULL default '',
  `creation_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `hpin` varchar(255) default NULL,
  `a1` varchar(32) default NULL,
  `a2` varchar(32) default NULL,
  `a3` varchar(32) default NULL,
  `s1` varchar(255) default NULL,
  `s2` varchar(255) default NULL,
  `s3` varchar(255) default NULL,
  `s4` varchar(255) default NULL,
  PRIMARY KEY  (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `identity_providers` (
  `id` int(11) NOT NULL auto_increment,
  `source_id` varchar(40) NOT NULL default '',
  `name` varchar(80) NOT NULL default '',
  `domain` varchar(64) default NULL,
  `logouturl` varchar(128) default NULL,
  `website` varchar(64) default NULL,
  `format` varchar(64) default NULL,
  `png16x16` blob,
  `display_login` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `source_id` (`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `inbox` (
  `inbox_id` int(10) unsigned NOT NULL auto_increment,
  `inbox_name` varchar(45) default NULL,
  `inbox_type` int(10) unsigned default NULL,
  `inbox_location` varchar(200) default NULL,
  PRIMARY KEY  (`inbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `log` (
  `content` varchar(255) NOT NULL default '',
  `time` time NOT NULL default '00:00:00',
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `log_entries` (
  `id` int(11) NOT NULL auto_increment,
  `datetime` datetime NOT NULL,
  `source_id` int(11) NOT NULL,
  `severity` varchar(1) NOT NULL,
  `message` varchar(256) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `log_entries_datetime` (`datetime`),
  KEY `log_entries_source_id` (`source_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `log_sources` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(16) NOT NULL,
  `path` varchar(256) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `log_sources_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `mcproperties` (
  `property` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `infourl` varchar(255) NOT NULL default '',
  `comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`property`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='MedCommons System Parameters';
CREATE TABLE `node` (
  `node_id` bigint(20) NOT NULL auto_increment,
  `admin_id` varchar(32) default NULL,
  `e_key` bigint(20) default NULL,
  `m_key` bigint(20) default NULL,
  `display_name` varchar(64) default NULL,
  `hostname` varchar(64) default NULL,
  `fixed_ip` varchar(30) default NULL,
  `node_type` int(11) default NULL,
  `creation_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `logging_server` varchar(128) default NULL,
  `client_key` varchar(40) default NULL,
  PRIMARY KEY  (`node_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=latin1;
CREATE TABLE `node_right` (
  `node_node_id` bigint(20) NOT NULL default '0',
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `rights` varchar(32) default NULL,
  PRIMARY KEY  (`node_node_id`,`groups_group_number`),
  KEY `NodeRights_FKIndex1` (`node_node_id`),
  KEY `NodeRights_FKIndex2` (`groups_group_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `personas` (
  `accid` decimal(16,0) NOT NULL default '0',
  `persona` varchar(32) NOT NULL default '',
  `personanum` tinyint(4) NOT NULL default '0',
  `personagif` varchar(255) NOT NULL default '',
  `isactive` tinyint(4) NOT NULL default '0',
  `phone` varchar(255) NOT NULL default '',
  `exposephone` tinyint(4) NOT NULL default '0',
  `inheritphone` tinyint(4) NOT NULL default '0',
  `myid` varchar(255) NOT NULL default '',
  `exposemyid` tinyint(4) NOT NULL default '0',
  `inheritmyid` tinyint(4) NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `exposeemail` tinyint(4) NOT NULL default '0',
  `inheritemail` tinyint(4) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `exposename` tinyint(4) NOT NULL default '0',
  `inheritname` tinyint(4) NOT NULL default '0',
  `address` varchar(255) NOT NULL default '',
  `exposeaddress` tinyint(4) NOT NULL default '0',
  `inheritaddress` tinyint(4) NOT NULL default '0',
  `dob` varchar(255) NOT NULL default '',
  `exposedob` tinyint(4) NOT NULL default '0',
  `inheritdob` tinyint(4) NOT NULL default '0',
  `sex` varchar(255) NOT NULL default '',
  `exposesex` tinyint(4) NOT NULL default '0',
  `inheritsex` tinyint(4) NOT NULL default '0',
  `ccrsectionconsents` varchar(255) NOT NULL default '',
  `qualitativeandmultichoice` varchar(255) NOT NULL default '',
  `distancecalcmin` varchar(255) NOT NULL default '',
  `nooldccrs` tinyint(4) NOT NULL default '0',
  `excluderefs` tinyint(4) NOT NULL default '0',
  `requiresms` tinyint(4) NOT NULL default '0',
  `promptmissing` tinyint(4) NOT NULL default '0',
  `mergeccr` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`accid`,`persona`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='persona';
CREATE TABLE `practice` (
  `practiceid` int(11) NOT NULL auto_increment,
  `practicename` varchar(32) NOT NULL default '',
  `providergroupid` int(11) NOT NULL default '0',
  `practiceRlsUrl` varchar(255) NOT NULL default '',
  `practiceLogoUrl` varchar(255) NOT NULL default '',
  `accid` decimal(16,0) NOT NULL default '0',
  PRIMARY KEY  (`practiceid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Defines a Practice';
CREATE TABLE `practiceccrevents` (
  `practiceid` int(11) default NULL,
  `PatientGivenName` varchar(64) NOT NULL default '',
  `PatientFamilyName` varchar(64) NOT NULL default '',
  `PatientIdentifier` varchar(64) NOT NULL default '',
  `PatientIdentifierSource` varchar(64) NOT NULL default '',
  `Guid` varchar(64) NOT NULL default '',
  `Purpose` varchar(64) NOT NULL default '',
  `SenderProviderId` varchar(64) NOT NULL default '',
  `ReceiverProviderId` varchar(64) NOT NULL default '',
  `DOB` varchar(64) NOT NULL default '',
  `CXPServerURL` varchar(255) NOT NULL default '',
  `CXPServerVendor` varchar(255) NOT NULL default '',
  `ViewerURL` varchar(255) NOT NULL default '',
  `Comment` varchar(255) NOT NULL default '',
  `CreationDateTime` bigint(20) NOT NULL default '0',
  `ConfirmationCode` varchar(64) NOT NULL default '',
  `RegistrySecret` varchar(64) NOT NULL default '',
  `PatientSex` varchar(64) default NULL,
  `PatientAge` varchar(64) default NULL,
  `Status` varchar(30) default NULL,
  `ViewStatus` varchar(20) default NULL,
  KEY `ccrevents_status_idx` (`Status`),
  KEY `groupinstanceid` (`practiceid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `rights` (
  `rights_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` varchar(32) default NULL,
  `document_id` int(10) unsigned default NULL,
  `rights` varchar(32) NOT NULL default '',
  `creation_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `expiration_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `rights_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `storage_account_id` varchar(32) default NULL,
  `active_status` varchar(32) NOT NULL default 'Active',
  `es_id` int(9) default NULL,
  PRIMARY KEY  (`rights_id`),
  KEY `Rights_FKIndex2` (`account_id`),
  KEY `Right_FKIndex3` (`document_id`),
  KEY `idx_rights_es_id` (`es_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `rssheadlines` (
  `id` int(11) NOT NULL auto_increment,
  `sourceid` smallint(6) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `description` tinytext NOT NULL,
  `pubDate` varchar(255) NOT NULL default '',
  `time` time NOT NULL default '00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='entires copied from rss feeds';
CREATE TABLE `rsssources` (
  `id` smallint(6) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `copyright` varchar(255) NOT NULL default '',
  `language` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `webMaster` varchar(255) NOT NULL default '',
  `managingEditor` varchar(255) NOT NULL default '',
  `rssversion` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='details on rss sources';
CREATE TABLE `security_certificate` (
  `id` int(11) NOT NULL auto_increment,
  `issued` datetime NOT NULL,
  `CN` varchar(64) NOT NULL,
  `C` varchar(2) NOT NULL,
  `ST` varchar(64) NOT NULL,
  `L` varchar(64) NOT NULL,
  `O` varchar(64) NOT NULL,
  `OU` varchar(64) NOT NULL,
  `key` longtext NOT NULL,
  `csr` longtext NOT NULL,
  `crt` longtext,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `todir` (
  `id` int(11) NOT NULL auto_increment,
  `groupid` int(11) NOT NULL default '0',
  `xid` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `contactlist` varchar(255) NOT NULL default '',
  `sharedgroup` tinyint(4) NOT NULL default '0',
  `pinstate` tinyint(4) NOT NULL default '0',
  `accid` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='holds mappings for to and replyto fields';
CREATE TABLE `tracking_number` (
  `tracking_number` varchar(64) NOT NULL default '',
  `rights_id` int(10) unsigned NOT NULL default '0',
  `encrypted_pin` varchar(64) default NULL,
  `pin` varchar(16) default NULL,
  `expiration_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `es_id` int(10) unsigned default NULL,
  `doc_id` int(10) default NULL,
  PRIMARY KEY  (`tracking_number`),
  KEY `TrackingNumber_FKIndex1` (`rights_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `user` (
  `medcommons_user_id` varchar(32) NOT NULL default '',
  `telephone_number` varchar(64) default NULL,
  `email_address` varchar(64) default NULL,
  `credential` blob,
  `creation_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `last_access_time` timestamp NOT NULL default '0000-00-00 00:00:00',
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
  `cert_checked` timestamp NOT NULL default '0000-00-00 00:00:00',
  `wired_ipaddress` varchar(255) default NULL,
  `wired_useragent` varchar(255) default NULL,
  PRIMARY KEY  (`medcommons_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `user_group` (
  `user_medcommons_user_id` varchar(32) NOT NULL default '',
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `user_role_with_group` varchar(32) NOT NULL default '',
  `added_by_id` varchar(32) default NULL,
  PRIMARY KEY  (`user_medcommons_user_id`,`groups_group_number`),
  KEY `UserGroup_FKIndex1` (`user_medcommons_user_id`),
  KEY `UserGroup_FKIndex2` (`groups_group_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `users` (
  `mcid` decimal(16,0) NOT NULL default '0',
  `email` varchar(64) default NULL,
  `sha1` varchar(40) default NULL,
  `server_id` mediumint(9) NOT NULL default '0',
  `since` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `first_name` varchar(32) default NULL,
  `last_name` varchar(32) default NULL,
  `mobile` varchar(64) default NULL,
  `smslogin` tinyint(4) default NULL,
  `updatetime` int(11) NOT NULL default '0',
  `ccrlogupdatetime` int(11) NOT NULL default '0',
  `chargeclass` varchar(255) default NULL,
  `rolehack` varchar(255) default NULL,
  `affiliationgroupid` int(11) default NULL,
  `startparams` varchar(255) default NULL,
  `stylesheetUrl` varchar(255) default NULL,
  `picslayout` varchar(255) default NULL,
  `photoUrl` varchar(255) default NULL,
  `acctype` varchar(255) default 'SPONSORED',
  `persona` varchar(255) default NULL,
  `validparams` varchar(255) default NULL,
  `interests` varchar(255) default NULL,
  `email_verified` datetime default NULL,
  `mobile_verified` datetime default NULL,
  `enc_skey` char(12) default NULL,
  `amazon_user_token` varchar(120) default NULL,
  `amazon_product_token` text,
  PRIMARY KEY  (`mcid`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `workflow_item` (
  `wi_id` int(11) NOT NULL auto_increment,
  `wi_source_account_id` varchar(32) default NULL,
  `wi_target_account_id` varchar(32) default NULL,
  `wi_type` varchar(30) NOT NULL,
  `wi_status` varchar(30) default NULL,
  `wi_key` varchar(60) default NULL,
  `wi_active_status` varchar(30) NOT NULL default 'Active',
  `wi_create_date_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`wi_id`),
  UNIQUE KEY `workflow_item_key_idx` (`wi_source_account_id`,`wi_target_account_id`,`wi_key`),
  KEY `workflow_item_source_account_idx` (`wi_source_account_id`),
  KEY `workflow_item_target_account_idx` (`wi_target_account_id`),
  KEY `workflow_item_status_idx` (`wi_status`),
  KEY `workflow_item_type_idx` (`wi_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='workflow data eg. tracking dicom downloads';
CREATE TABLE `worklist_queue` (
  `worklist_id` int(10) unsigned NOT NULL auto_increment,
  `groups_group_number` int(10) unsigned NOT NULL default '0',
  `user_medcommons_user_id` varchar(32) NOT NULL default '',
  `description` varchar(32) default NULL,
  PRIMARY KEY  (`worklist_id`),
  KEY `WorklistQueue_FKIndex1` (`user_medcommons_user_id`),
  KEY `WorklistQueue_FKIndex2` (`groups_group_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE `worklist_queue_item` (
  `rights_id` int(10) unsigned NOT NULL default '0',
  `worklist_queue_worklist_id` int(10) unsigned NOT NULL default '0',
  `placed_in_queue` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `order_number` int(10) unsigned default NULL,
  `priority` int(10) unsigned default NULL,
  PRIMARY KEY  (`rights_id`,`worklist_queue_worklist_id`),
  KEY `WorklistQueueItem_FKIndex1` (`worklist_queue_worklist_id`),
  KEY `WorklistQueueItem_FKIndex2` (`rights_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT;
SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS;
SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION;
SET SQL_NOTES=@OLD_SQL_NOTES;
