create table phone_authentication (
  pa_id int(10) unsigned auto_increment,
  pa_phone_number varchar(20) NOT NULL,
  pa_access_code varchar(20) NOT NULL,
  pa_active_status varchar(12) NOT NULL default 'Active',
  pa_create_date_time timestamp NOT NULL default CURRENT_TIMESTAMP,
  primary key (pa_id)
);
create index pa_phone_number_idx on phone_authentication (pa_phone_number);
create index pa_create_date_time_idx on phone_authentication (pa_create_date_time);
