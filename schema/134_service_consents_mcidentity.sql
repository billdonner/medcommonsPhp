drop table modservice_consent;
create table modservice_consents (
  svcnum int(11),
  accid decimal(16,0),
  primary key (svcnum,accid)
);
