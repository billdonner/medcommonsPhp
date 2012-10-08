create table modcoupon_share (
  accid decimal(16,0),
  couponum int(11),
  primary key (accid,couponum)
);

create index modcoupon_share_couponum_idx on modcoupon_share (couponum);

create index modcoupon_share_accid_idx on modcoupon_share (accid);
