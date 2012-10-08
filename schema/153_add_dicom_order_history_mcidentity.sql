CREATE TABLE `dicom_order_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `version` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `ddl_status` varchar(30) NOT NULL,
  `description` varchar(255) NOT NULL,
  `dicom_order_id` bigint(20) NOT NULL,
  `remote_host` varchar(255) DEFAULT NULL,
  `remote_ip` varchar(60) NOT NULL,
  `remote_user` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK3691F0807783AFA1` (`dicom_order_id`)
)
