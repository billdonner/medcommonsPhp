--- requirements mean that there are use cases where account must be created but
--- no login via that account should be allowed. For this case it is useful and
--- intuitive to make the password null
alter table users change column sha1 sha1 varchar(40);
