--- failed merges need to be tracked so that when the user next logs in
--- they can be flagged.  This status flag enables the account server to
--- know which CCRs are awaiting merge resolution.
alter table ccrlog add column merge_status varchar(32);
