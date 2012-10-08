DELETE FROM  `modsvctemplates`;

CREATE TABLE IF NOT EXISTS `modsvctemplates` (
  `templatenum` mediumint(9) NOT NULL,
  `servicename` varchar(255) NOT NULL,
  `servicedescription` varchar(255) NOT NULL,
  `displayhtml` mediumtext NOT NULL,
  `printhtml` mediumtext NOT NULL,
  `duration` tinyint(4) NOT NULL,
  `asize` tinyint(4) NOT NULL,
  `fcredits` tinyint(4) NOT NULL,
  `dcredits` tinyint(4) NOT NULL,
  PRIMARY KEY  (`templatenum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modsvctemplates`
--

INSERT INTO `modsvctemplates` (`templatenum`, `servicename`, `servicedescription`, `displayhtml`, `printhtml`, `duration`, `asize`, `fcredits`, `dcredits`) VALUES
(-5, 'Complete Health Record', 'patient initiated request for complete health record', '', '', 1, 1, 0, 0),
(-4, 'Immunizations', 'patient initiated request for immunization records', '', '', 1, 1, 0, 0),
(-3, 'Medications', 'patient initiated request for medications', '', '', 1, 1, 0, 0),
(-2, 'Current Summary', 'patient request for medical records summary', '', '', 1, 1, 0, 0),
(-1, 'Patient ROI Request', 'online request for records', '', '', 0, 0, 0, 0),
(0, 'New Generic Service', 'enter description here', '', '', 0, 0, 0, 0),
(2, 'Current Summary', 'patient request for medical records summary', '', '', 1, 1, 0, 0),
(3, 'Medications', 'patient initiated request for medications', '', '', 1, 1, 0, 0),
(4, 'Immunizations', 'patient initiated request for immunization records', '', '', 1, 1, 0, 0),
(5, 'Complete Health Record', 'patient initiated request for complete health record', '', '', 1, 1, 0, 0);
