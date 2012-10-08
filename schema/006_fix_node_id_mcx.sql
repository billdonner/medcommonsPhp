alter table node     modify column node_id bigint(20) not null unique;
create unique index document_guid_idx on document(guid);
create unique index document_location_node_idx on document_location(document_id, node_node_id);