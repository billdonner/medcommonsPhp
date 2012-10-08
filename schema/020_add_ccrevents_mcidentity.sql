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
  `RegistrySecret` varchar(64) NOT NULL default ''
) TYPE=MyISAM;
