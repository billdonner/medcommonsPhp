alter table dicom_status add column ds_type varchar(30);
alter table dicom_status add column ds_message text;
update  dicom_status set ds_type = 'UPLOAD' where ds_status = 'Uploading';
update dicom_status set ds_type = 'DOWNLOAD' where ds_status = 'Downloading';
alter table dicom_status modify column ds_type varchar(30) NOT NULL;
alter table dicom_status add column ds_owner_account_id decimal(16,0);
