alter table document drop index document_guid_idx;
create index document_guid_idx on document (guid);
create index document_storage_idx on document (storage_account_id);
