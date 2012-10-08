create table workflow_item (
  wi_id int(11) not null auto_increment,
  wi_source_account_id varchar(32),
  wi_target_account_id varchar(32),
  wi_type varchar(30) not null,
  wi_status varchar(30),
  wi_key varchar(60),
  wi_active_status varchar(30) not null default 'Active',
  wi_create_date_time timestamp not null default CURRENT_TIMESTAMP,
  primary key(wi_id)
) engine=INNODB, comment='workflow data eg. tracking dicom downloads';

create index workflow_item_source_account_idx on workflow_item(wi_source_account_id);
create index workflow_item_target_account_idx on workflow_item(wi_target_account_id);
create index workflow_item_status_idx on workflow_item(wi_status);
create index workflow_item_type_idx on workflow_item(wi_type);
create unique index workflow_item_key_idx on workflow_item(wi_source_account_id,wi_target_account_id,wi_key);
