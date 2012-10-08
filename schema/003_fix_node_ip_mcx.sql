alter table node modify column node_id bigint(20) not null auto_increment;
alter table node modify column fixed_ip varchar(30);
create unique index node_fixed_ip_idx on node (fixed_ip);
