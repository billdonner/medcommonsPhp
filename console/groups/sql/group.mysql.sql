
--
-- Table structure for table `groupmembers`
--

CREATE TABLE groupmembers (
  groupinstanceid int(11) NOT NULL default '0',
  memberaccid decimal(16,0) NOT NULL default '0',
  comment varchar(255) NOT NULL default '',
  PRIMARY KEY  (groupinstanceid,memberaccid),
  KEY adminaccid (memberaccid)
) ENGINE=InnoDB COMMENT='every member of any group';

