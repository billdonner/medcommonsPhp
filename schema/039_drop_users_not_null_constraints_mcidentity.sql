--- 4.6 Unclaimed Accounts don't (necessarily) have any information about the user in them. 
--- Hence we need to be able to store null in email, first_name and last_name
alter table users change email email varchar(64);
alter table users change first_name first_name varchar(32);
alter table users change last_name last_name varchar(32);
