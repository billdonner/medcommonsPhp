create table document_charge (
  dc_id int(12) not null auto_increment,
  dc_document_id int(12),
  dc_charge_type varchar(30), 
  dc_quantity int(9),  
  dc_status varchar(30),
  dc_create_date_time timestamp not null default CURRENT_TIMESTAMP,
  primary key(dc_id)
);
