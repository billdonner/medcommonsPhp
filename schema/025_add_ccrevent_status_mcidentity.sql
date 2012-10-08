alter table ccrevents add column Status varchar(30);
create index ccrevents_status_idx on ccrevents (Status);
