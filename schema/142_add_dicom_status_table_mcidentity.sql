create table dicom_status (
  ds_key varchar(40),
  ds_account_id decimal(16,0),
  ds_status varchar(60),
  ds_progress decimal(6,3),
  ds_modified_date_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ds_create_date_time timestamp NOT NULL,
  primary key (ds_key)
) ENGINE=InnoDB;

create index ds_account_id_idx on dicom_status (ds_account_id);
