alter table tracking_number add column es_id int(10) unsigned;
alter table tracking_number add column doc_id int(10);
alter table external_share add column es_create_date_time timestamp NOT NULL default CURRENT_TIMESTAMP;
update tracking_number t, rights r set t.doc_id = r.document_id where r.rights_id = t.rights_id and r.document_id is not null;
