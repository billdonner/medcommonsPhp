---
--- ssadedin: a whole mashup of changes to try and synchronize with 
--- bills prototyping of groups 
---

CREATE TABLE groupadmins (
  groupinstanceid int(11) NOT NULL default '0',
  adminaccid decimal(16,0) NOT NULL default '0',
  comment varchar(255) NOT NULL default '',
  PRIMARY KEY  (groupinstanceid,adminaccid),
  KEY memberaccid (adminaccid)
) TYPE=MyISAM COMMENT='every member of any group';

--
-- Table structure for table `groupccrevents`
--

CREATE TABLE groupccrevents (
  groupinstanceid int(11) NOT NULL default '0',
  PatientGivenName varchar(64) NOT NULL default '',
  PatientFamilyName varchar(64) NOT NULL default '',
  PatientIdentifier varchar(64) NOT NULL default '',
  PatientIdentifierSource varchar(64) NOT NULL default '',
  Guid varchar(64) NOT NULL default '',
  Purpose varchar(64) NOT NULL default '',
  SenderProviderId varchar(64) NOT NULL default '',
  ReceiverProviderId varchar(64) NOT NULL default '',
  DOB varchar(64) NOT NULL default '',
  CXPServerURL varchar(255) NOT NULL default '',
  CXPServerVendor varchar(255) NOT NULL default '',
  ViewerURL varchar(255) NOT NULL default '',
  Comment varchar(255) NOT NULL default '',
  CreationDateTime bigint(20) NOT NULL default '0',
  ConfirmationCode varchar(64) NOT NULL default '',
  RegistrySecret varchar(64) NOT NULL default '',
  PatientSex varchar(64) default NULL,
  PatientAge varchar(64) default NULL,
  Status varchar(30) default NULL,
  KEY ccrevents_status_idx (Status),
  KEY groupinstanceid (groupinstanceid)
) TYPE=MyISAM;

--
-- Table structure for table `groupinstances`
--

CREATE TABLE groupinstances (
  groupinstanceid int(11) NOT NULL default '0',
  grouptypeid int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  groupLogo varchar(255) NOT NULL default '',
  adminUrl varchar(255) NOT NULL default '',
  memberUrl varchar(255) NOT NULL default '',
  parentid int(11) NOT NULL default '0',
  PRIMARY KEY  (groupinstanceid),
  KEY name (name)
) TYPE=MyISAM COMMENT='one row for each practice group or sig group...';

--
-- Table structure for table `groupmembers`
--

CREATE TABLE groupmembers (
  groupinstanceid int(11) NOT NULL default '0',
  memberaccid decimal(16,0) NOT NULL default '0',
  comment varchar(255) NOT NULL default '',
  PRIMARY KEY  (groupinstanceid,memberaccid),
  KEY adminaccid (memberaccid)
) TYPE=MyISAM COMMENT='every member of any group';

--
-- Table structure for table `groupproperties`
--

CREATE TABLE groupproperties (
  groupinstanceid int(11) NOT NULL default '0',
  property varchar(255) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  comment varchar(255) NOT NULL default '',
  PRIMARY KEY  (groupinstanceid,property)
) TYPE=MyISAM COMMENT='properties for specific group types';

--
-- Table structure for table `grouptypes`
--

CREATE TABLE grouptypes (
  grouptypeid int(11) NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  infoUrl varchar(255) NOT NULL default '',
  rulesUrl varchar(255) NOT NULL default '',
  supportPageUrl varchar(255) NOT NULL default '',
  internalgroup tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (grouptypeid),
  KEY name (name)
) TYPE=MyISAM COMMENT='one row for each type of MedCommons Group';


CREATE TABLE practice (
  practiceid int(11) NOT NULL default '0',
  practicename varchar(32) NOT NULL default '',
  providergroupid int(11) NOT NULL default '0',
  patientgroupid int(11) NOT NULL default '0',
  practiceRlsUrl varchar(255) NOT NULL default '',
  practiceLogoUrl varchar(255) NOT NULL default '',
  accid decimal(16,0) NOT NULL default '0',
  PRIMARY KEY  (practiceid)
) TYPE=MyISAM COMMENT='Defines a Practice';

CREATE TABLE appeventlog (
  accid varchar(16) NOT NULL default '',
  appserviceid varchar(32) NOT NULL default '',
  eventname varchar(255) NOT NULL default '',
  param1 varchar(255) NOT NULL default '',
  time int(11) NOT NULL default '0',
  chargeclass varchar(255) NOT NULL default ''
) TYPE=MyISAM COMMENT='log of billable events';

CREATE TABLE appservicechargeclasses (
  appserviceid varchar(32) NOT NULL default '',
  chargeclass varchar(255) NOT NULL default '',
  permonth int(11) NOT NULL default '0',
  perclick int(11) NOT NULL default '0',
  perxmtgb int(11) NOT NULL default '0',
  perrcvgb int(11) NOT NULL default '0',
  setup int(11) NOT NULL default '0',
  perstoredgb int(11) NOT NULL default '0',
  PRIMARY KEY  (appserviceid,chargeclass)
) TYPE=MyISAM COMMENT='pricing for this service';

CREATE TABLE appservicecontracts (
  accid varchar(16) NOT NULL default '',
  appserviceid varchar(32) NOT NULL default '',
  time time default NULL,
  PRIMARY KEY  (accid,appserviceid)
) TYPE=MyISAM;

CREATE TABLE appservicedependencies (
  appserviceid varchar(32) NOT NULL default '',
  dependson varchar(32) NOT NULL default '',
  PRIMARY KEY  (appserviceid,dependson)
) TYPE=MyISAM COMMENT='details appservice loading/unloading dependencies';

CREATE TABLE appservices (
  name varchar(255) NOT NULL default '',
  serviceurl varchar(255) NOT NULL default '',
  publisher varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  appserviceid varchar(32) NOT NULL default '',
  createurl varchar(255) NOT NULL default '',
  removeurl varchar(255) NOT NULL default '',
  viewurl varchar(255) NOT NULL default '',
  builtin varchar(255) NOT NULL default '',
  PRIMARY KEY  (appserviceid)
) TYPE=MyISAM COMMENT='3rd party apps and services ';

alter table users add column chargeclass varchar(255);
alter table users add column trackerdb varchar(255);
alter table users add column rolehack varchar(255);
alter table users add column affiliationgroupid int(11);
alter table users add column startparams varchar(255);
