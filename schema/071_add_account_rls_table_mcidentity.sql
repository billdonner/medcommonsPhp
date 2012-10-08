create table account_rls (
  ar_accid varchar(32),
  ar_rls_url text not null,
  primary key (ar_accid)
) engine=INNODB, comment='registries to which account update notifications will be sent';
