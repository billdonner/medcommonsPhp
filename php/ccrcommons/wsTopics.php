<?php

require_once "setup.inc.php";
// Topics Web Service, As Simple as Possible
function err_exit($x)
{
	echo
	<<<XXX
	<?xml version='1.0' encoding='UTF-8'?>
	<topics>
	<status>0</status>
	<reason>$x</reason>
	</topics>	
XXX;

	exit;
}
function mformat($hurl,$comment,$authorid,$groupid,$topic,$topicid,$time)

{
	return <<<XXX
<publicHealthURL>
  <HealthURL>$hurl</HealthURL>
  <Comment>$comment</Comment>
  <AuthorID>$authorid</AuthorID>
  <GroupID>$groupid</GroupID>
  <TopicID>$topicid</TopicID>
  <Topic>$topic</Topic>
  <Time>$time</Time>
</publicHealthURL>
XXX;
}
function topicHurls($ord)
{
	$buf ='';
	$q = "select * from topichurls,topics where topic='$ord' and topic=ord order by time desc limit 50";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	while ($r=mysql_fetch_object($result))
	{
		$buf.=mformat($r->hurl,$r->comment,$r->authorfbid,$r->groupid,$r->nlmtopic,$r->ord,$r->time);
	}

	return $buf;
}
function getTopicHurls($ord)
{
	$b = getTopicInfo($ord);
	$stuff=''; $eaches = explode(',',$ord);
	foreach  ($eaches as $ach)
	{
		$foo = topicHurls($ach);
		$stuff .= <<<XXX
	<topic ord='$ach'>
	<name>$b->nlmtopic</name>
	<nlmlink>$b->nlmurl</nlmlink>
	$foo
	</topic>	
XXX;
}
return $stuff;
}
function isPublicAppliance($h)
{
	$h = substr($h,0,strlen($h)-16);
	$q="Select * from publicappliances where applianceurl = '$h' ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$count = mysql_num_rows($result);
	//echo "$h is public appliance $count ";
	return ($count>0);

}
function checkHealthURL($h)
{
	function isdig16($s)
	{
		for ($i=0;$i<16;$i++)
		{
			$c = substr($s,$i,1);
			if (('0'>$c)|| ($c>'9')) return false;
		}
		return true;
	}
	// right now just check to make sure it ends with all digits, 16 of them
	if  (isPublicAppliance($h)) //((substr($h,0,7)=='http://')||(substr($h,0,8)=='https://'))
	{
		$len = strlen($h);
		if (isdig16(substr($h,$len-16))) return true;
	}
	return false;
}
function getTopicInfo($ord)
{
	$q = "SELECT * from topics where ord='$ord'";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	$b=mysql_fetch_object($result);
	return $b;
}

function connect_db()
{
	// no longer needed now that appinclude.php must connect
	mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User']) or err("Error connecting to database.");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die("can not connect to database $db");
	
}
connect_db();
if (!isset($_REQUEST['ord'])) err_exit("Needs Topic ord"); else $ord = $_REQUEST['ord'];
if (!isset($_REQUEST['healthURL']))
{
	//return all the healthURLs posted to this topic

	
	$hurls = getTopicHurls($ord);
	$outmsg = <<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<healthbook_api>
<topics>
<status>1</status>
<request_info>ord=$ord</request_info>
$hurls
</topics>
</healthbook_api>
XXX;
	header("Content-type: text/xml");
	echo $outmsg;
}
else
{
	// we got posted to here, $ord is already set

	if (!isset($_REQUEST['tagline'])) err_exit("Needs Tagline");
	$healthurl = $_REQUEST['healthURL']; // required
	$tagline = $_REQUEST['tagline']; //required
	if (isset($_REQUEST['gid'])) //optional
	$gid = $_REQUEST['gid'];	else $gid=0;
	if (isset($_REQUEST['fbid'])) //optional
	$fbid = $_REQUEST['fbid'];	else $fbid=0;

	$b=getTopicInfo($ord);
	// check whether these healthurls look any good at all
	if (!checkHealthURL($healthurl)) err_exit("Bad Public HealthURL");

	$hcp1 = $b->hurlcount+1;
	$time = time();
	$q="replace into  topichurls set time='$time', topic='$ord', hurl='$healthurl',comment='$tagline',groupid='$gid',authorfbid='$fbid'";
	mysql_query($q) or die("Cant $q ".mysql_error()); // should really check to see
	$q="update topics set hurlcount='$hcp1' where ord='$ord'";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	header("Content-type: text/xml");
	echo
	<<<XXX
<?xml version='1.0' encoding='UTF-8' ?>
<healthbook_api>
 <topics>
 <status>1</status>
 <reason>successfully posted $healthurl  to $ord</reason>
 </topics>
</healthbook_api>	
XXX;
}

?>


