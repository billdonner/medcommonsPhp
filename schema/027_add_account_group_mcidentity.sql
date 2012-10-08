CREATE TABLE account_group ( 
  id decimal(16,0) NOT NULL,
  name varchar(255),
  registry varchar(255),
  directory varchar(255),
  create_date_time timestamp NOT NULL,
  primary key (id)
)
COMMENT='Holds settings for accounts on a group basis'; 
