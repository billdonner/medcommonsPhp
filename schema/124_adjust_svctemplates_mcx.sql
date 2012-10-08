--
-- Dumping data for table `modsvctemplates`
--
Delete from `modsvctemplates`;
INSERT INTO `modsvctemplates` (`templatenum`, `servicename`, `servicedescription`, `displayhtml`, `printhtml`, `duration`, `asize`, `fcredits`, `dcredits`) VALUES
(-1, 'Patient ROI Request', 'patient initiated request for records', '', '', 1, 1, 0, 0),
(0, 'New Generic Service', 'enter description here', '', '', 0, 0, 0, 0),
(1, 'sonogram online', 'patient sonogram uploaded and available online', '', '', 4, 3, 0, 0),
(2, 'mamogram online', 'patient mamorgram uploaded and available online', '', '', 4, 2, 0, 0),
(3, 'Tumor Metrics', 'RECIST Protocol for Lung Cancer', '', '', 2, 0, 0, 0),
(4, 'Diagnostic Imaging Release', 'Chest XRay and CT', '', '', 1, 3, 0, 0),
(5, 'Results Report', 'Laboratory and Radiology Report', '', '', 3, 1, 0, 0);