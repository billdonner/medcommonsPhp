<?php
require_once "healthbook.inc.php";
// Topics Web Service, As Simple as Possible 
function err_exit($x)
{
	echo
	<<<XXX
	<?xml version='1.0' encoding='UTF-8'?><topics>
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

connect_db();
if (!isset($_REQUEST['ord'])) err_exit("Needs Topic ord");
$ord = $_REQUEST['ord'];
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

?>


