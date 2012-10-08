alter table modcoupons add column   `fcredits` tinyint(4) NOT NULL;
alter table modcoupons add column   `dcredits` tinyint(4) NOT NULL;
alter table modcoupons add column   `asize` tinyint(4) NOT NULL;
alter table modservices add column   `fcredits` tinyint(4) NOT NULL;
alter table modservices add column   `dcredits` tinyint(4) NOT NULL;
alter table modsvctemplates add column   `fcredits` tinyint(4) NOT NULL;
alter table modsvctemplates add column   `dcredits` tinyint(4) NOT NULL;
