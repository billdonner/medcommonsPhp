create table authentication_token (
    at_id int(10) unsigned auto_increment,
    at_token varchar(40),
    at_account_id varchar(32),
    at_create_date_time TIMESTAMP,
    primary key (at_id)
);

create  index idx_at_token on authentication_token ( at_token );
