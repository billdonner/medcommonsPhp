<?php
//get the tracker db associated with this accound, or return ''
require_once "dbparamsidentity.inc.php";

function getTrackerDb ($accid)
{
	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	mysql_select_db($db) or die ("can not connect to database $db");

	$query = "SELECT * FROM users WHERE (mcid = '$accid')";
	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$record = mysql_fetch_array($result,MYSQL_ASSOC);
	mysql_free_result($result);
	return $record['trackerdb'];
}
function setTrackerDb ($url)
{	$accid=get_accid();
	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	mysql_select_db($db) or die ("can not connect to database $db");

	$query = "UPDATE  users SET trackerdb='$url' WHERE mcid = '$accid'";
	$result = mysql_query ($query) or die("can not update table users - $query".mysql_error());

}
function get_accid()
{
	// if running remotely, require that we are logged on and get our account id
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$c1 = $_COOKIE['mc'];
	if ($c1!='')
	{	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$props = explode(',',$c1);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;
			case 'fn': $fn = $val; break;
			case 'ln': $ln = $val; break;
			case 'email'; $email = $val; break;
			case 'from'; $idp = stripslashes($val); break;

		}
	}
	}
	return $accid;
}
function get_tracker_db()
{$accid = get_accid();
if ($accid!=''){
	// ok, we have the account id, now go read the record to get the tracker db
	$db=getTrackerDb($accid);
	if ($db===false) die("No Tracker Database established for this account");
	return $db;

} else die ("You must be logged on to MedCommons to utilize Trackers");
}

function make_tracker_db_name()
{	$accid =  get_accid();
	return "/usr/local/share/trackers/$accid"."mytrackers.db";
	//return "../mytrackers.db";
}

function make_sparkline_log_name()
{	$accid =  get_accid();
	return "/usr/local/share/trackers/$accid"."sparklinelog.txt";
	//return "../sparklinelog.txt";
}
?>