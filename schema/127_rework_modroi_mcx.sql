drop table `modroi` ;


CREATE TABLE IF NOT EXISTS `modroi` (
  `ind` mediumint(9) NOT NULL auto_increment,
  `reqid` char(11) NOT NULL COMMENT 'similar to voucherid',
  `issued` date NOT NULL,
  `patientname` varchar(255) NOT NULL,
  `patientemail` varchar(255) NOT NULL,
  `patientdob` date NOT NULL,
  `patientnote` varchar(255) NOT NULL,
  `providername` varchar(255) NOT NULL,
  `provideremail` varchar(255) NOT NULL,
  `svcvec` char(32) NOT NULL default '00000000000000000000000000000000',
  PRIMARY KEY  (`ind`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Incoming ROI Requests, Not for any particular provider' AUTO_INCREMENT=35 ;
