alter table modservices add column `asize` tinyint(4) NOT NULL;
alter table modservices add column  `suggestedprice` int(11) NOT NULL;
alter table modservices add column  `servicelogo` varchar(255) NOT NULL;
CREATE TABLE `modsvctemplates` (
  `templatenum` mediumint(9) NOT NULL,
  `servicename` varchar(255) NOT NULL,
  `servicedescription` varchar(255) NOT NULL,
  `displayhtml` mediumtext NOT NULL,
  `printhtml` mediumtext NOT NULL,
  `duration` tinyint(4) NOT NULL,
  `asize` tinyint(4) NOT NULL,
  PRIMARY KEY  (`templatenum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

alter table modcoupons add column `voucherid` char(10) NOT NULL;
alter table modcoupons add column `issuetime` int(11) NOT NULL;

