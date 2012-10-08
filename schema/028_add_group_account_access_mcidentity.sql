CREATE TABLE group_account_access ( 
   account_id decimal(16,0) NOT NULL,
   group_id decimal(16,0) NOT NULL,
   primary key (account_id, group_id)
) comment='defines access to account records by a group';

CREATE TABLE account_group_users ( 
   account_group_id decimal(16,0) NOT NULL,
   users_id decimal(16,0) NOT NULL,
   primary key (account_group_id, users_id)
) comment='defines groups of users';

