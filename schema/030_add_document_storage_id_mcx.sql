alter table document add column storage_account_id varchar(32);
alter table rights change user_medcommons_user_id account_id varchar(32);
alter table rights add column storage_account_id varchar(32);
