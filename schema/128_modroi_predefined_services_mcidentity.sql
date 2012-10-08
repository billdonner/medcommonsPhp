alter table modroi drop column svcvec;
alter table modroi add column servicename varchar(120) NOT NULL;
alter table modroi add column svcnum int(9);
