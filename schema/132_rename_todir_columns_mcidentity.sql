alter table todir add column td_owner_accid varchar(16);
update todir d, groupinstances gi set d.td_owner_accid = gi.accid where gi.groupinstanceid = d.groupid;
alter table todir drop column groupid;
alter table todir change column alias td_alias varchar(255);
alter table todir change column xid td_xid varchar(255);
alter table todir change column contactlist td_contact_list varchar(255);
alter table todir change column sharedgroup td_shared_group tinyint(4);
alter table todir change column pinstate td_pin_state tinyint(4);
alter table todir change column accid td_contact_accid varchar(16);
