alter table users add column active_group_accid decimal(16,0);

update users
set active_group_accid = (
    select accid from groupinstances gi, groupmembers gm where gi.groupinstanceid =  gm.groupinstanceid and memberaccid = mcid limit 1
);

alter table users drop column dashboard_mode;
