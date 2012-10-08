ALTER TABLE node DROP INDEX node_id;

ALTER TABLE node DROP INDEX node_fixed_ip_idx;

ALTER TABLE document_location
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (id),
	ADD UNIQUE KEY (document_id, id, node_node_id);
