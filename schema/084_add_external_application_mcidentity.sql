create table external_application (
    ea_id int(9) not null auto_increment,
    ea_key varchar(255),
    ea_code varchar(30),
    ea_name varchar(255),
    ea_active_status varchar(30),
    ea_ip_address varchar(60),
    ea_create_date_time timestamp,
    primary key(ea_id)
)

