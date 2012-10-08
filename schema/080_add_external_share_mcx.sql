create table external_share (
  es_id int(9) not null auto_increment,
  es_identity text,
  es_identity_type varchar(30),
  primary key(es_id)
) engine=INNODB, comment='rights granted to non-medcommons accounts eg. open id';
create index idx_external_share_identity on external_share ( es_identity (255) );
alter table rights add column es_id int(9);
create index idx_rights_es_id on rights ( es_id );
alter table authentication_token add column at_es_id int(9);
