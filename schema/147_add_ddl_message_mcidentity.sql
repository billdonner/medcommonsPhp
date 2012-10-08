create table transfer_message (
  tm_id int(12) NOT NULL auto_increment,
  tm_ddl_identifier varchar(40),
  tm_transfer_key varchar(40),
  tm_account_id decimal(16,0),
  tm_message_category varchar(30),
  tm_message text NOT NULL,
  tm_create_date_time timestamp NOT NULL default CURRENT_TIMESTAMP,
  primary key (tm_id)
) ENGINE=InnoDB;

create index tm_ddl_identifier_idx on transfer_message (tm_ddl_identifier);
create index tm_transfer_key_idx on transfer_message (tm_transfer_key);
create index tm_create_date_time_idx on transfer_message (tm_create_date_time);
