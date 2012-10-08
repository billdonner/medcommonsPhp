
drop database if exists mcgateway;
create database mcgateway;
grant all on mcgateway.* to 'gateway'@'localhost';
grant all on mcgateway.* TO 'backup'@'localhost';
grant all on mcgateway.* TO 'gateway'@'localhost.localdomain';
grant select on mcx.users TO 'backup'@'localhost';

drop table if exists mcgateway.backup_queue;
create table mcgateway.backup_queue (
 `id` bigint(20) NOT NULL auto_increment,  
  `queuetime` timestamp(14),
  `starttime` datetime DEFAULT  null,
  `endtime` datetime DEFAULT null,
  `account_id` decimal(16,0) NOT NULL default '0',
  `guid` char(40) NOT NULL,
  `status` varchar(16) NOT NULL,
  `size` bigint(20) NOT NULL default '0',
  primary key (`id`)
) engine=INNODB, comment='queue for backups to offsite location (such as S3)';


