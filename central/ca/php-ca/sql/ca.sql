drop database if exists ca;

create database ca;

use ca;

drop table if exists certificates;

create table certificates (
  id          varchar(16) not null,
  cn          varchar(80) not null,
  email       varchar(80) not null,
  o	      varchar(80) not null,
  ou          varchar(80) not null,
  city        varchar(80) not null,
  state       varchar(80) not null,
  country     varchar(2)  not null,
  serial      varchar(16) not null,
  status      varchar(10) not null DEFAULT 'active',
  lastaccess  datetime
);



grant select, insert, update, delete
on ca.*
to ca@localhost identified by 'cyphertext';
