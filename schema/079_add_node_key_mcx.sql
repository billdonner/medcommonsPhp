alter table node add column client_key varchar(40);
alter table node modify column node_id bigint(20) not null auto_increment;
alter table node auto_increment = 1000;
