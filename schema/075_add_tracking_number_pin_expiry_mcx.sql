alter table tracking_number add column pin varchar(16);
alter table tracking_number add column expiration_time timestamp;
alter table cover add column cover_pin varchar(12);
