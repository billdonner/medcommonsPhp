<?php
//get the tracker db associated with this account, or return ''

function get_tracker_db ($accid)
{
return make_tracker_db_name($accid);
}
function setTrackerDb ($url)
{	
}

function make_tracker_db_name($accid)
{	
	return "/usr/local/share/trackers/$accid"."mytrackers.db";
	//return "../mytrackers.db";
}

function make_sparkline_log_name($accid)
{	
	return "/usr/local/share/trackers/$accid"."sparklinelog.txt";
	//return "../sparklinelog.txt";
}
?>