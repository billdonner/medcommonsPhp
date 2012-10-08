<?php
require_once "setup.inc.php";
function dosql($q)
{
	if (!isset($GLOBALS['db_connected']) ){
		$GLOBALS['db_connected'] =
		mysql_connect($GLOBALS['DB_Connection'] ,$GLOBALS['DB_User'] );
		$db = $GLOBALS['DB_Database'];
		mysql_select_db($db) or die ("can not connect to database $db ".mysql_error());
	}
	$status = mysql_query($q);
	if (!$status) die ("dosql failed $q ".mysql_error());
	return $status;
}
function getKEYWORDS($topic)
{
	return "'$topic->nlmxtra','$topic->nlmtopic',";
}
function getPHU($topic)
{
if ($topic->nlmxtra!='') $extra = "<p>The Medline metadata associated with this topic is : $topic->nlmxtra</p>"; else $extra = '';
	$buf ="<div id='ContentBoxInterior' mainTitle='HealthBookTopics' >
	<h2>Health Topic: $topic->nlmtopic</h2>
	<div id=medlineinfo >This topic is derived from the ontology at Medline. You can view the base
	<a href=$topic->nlmurl target='_new' title='open topic in new window on Medline' >medline page
	<img src='http://www.nlm.nih.gov/medlineplus/images/mpluslinksm.gif' /></a></div>
	  <div id=facebookinfo>You can view this on Facebook as  <span class='Topic'>
	    <a  href='http://apps.facebook.com/medcommons/topics.php?ord=$topic->ord' title='goto $topic->nlmtopic Facebook' target='_new' >
               <img src='http://photos-a.ak.facebook.com/photos-ak-sctm/v43/228/13002635244/app_2_13002635244_1764.gif' border='0' />$topic->nlmtopic</a>
             </span> </div>
    <span class='TopicID'>topic# $topic->ord</span> &nbsp;".$extra;
	

	$result=dosql("Select * from topichurls h,topics t where h.topic='$topic->ord' and t.ord='$topic->ord' order by h.ind desc limit 3 ");

	while ($rr = mysql_fetch_object($result))
	{
		$gid = $rr->groupid;
		$time = strftime('%D %T', $rr->time);
		$buf.=<<<XXX
<p> 
   <span><a target='_new'  title=' open public healthurl $rr->hurl' href='$rr->hurl' ><img border='0' src="http://www.medcommons.net/images/icon_healthURL.gif" /></a></span><span title='entry posted at $time'>$rr->comment</span><span class='AuthorID'><a target='_new' title="the author's profile page on facebook"  href='http://www.facebook.com/profile.php?id=$rr->authorfbid' ><img src='http://www.medcommons.net/images/fbsmall.jpg'  border='0' /></a>
<a href='mailto:report@medcommons.net'>report abuse</a></span>
</p>
XXX;
}
if ($buf!='') $buf = "$buf</div>";
if ($buf=='') return  "<div><p>There are no Public HealthURLs associated with this topic.</p></div>";
return $buf;
}
// starts here
if (!isset($_REQUEST['ord']))
{	// if not specified, show all
	require_once "searchbox.inc.php";
	$ssbox = standard_searchbox();
	$bigbuf = file_get_contents($TEMPLATE_OUTER_FRAME);
	$insides = file_get_contents ($MASTER_TOPICS_FILE);
	$bigbuf = str_replace ('**TITLE**',"CCR Commons Public Topic Directory ",$bigbuf);
	$bigbuf = str_replace('**BODY**',$ssbox.$insides,$bigbuf);// find this pattern on a standard page
	echo $bigbuf;
	exit;
}
// do a topic specific lookup
$ord = $_REQUEST['ord'];
$result = dosql ("Select * from topics where ord='$ord' ");
$topic = mysql_fetch_object($result);
$middle = getPHU($topic);
$keywords = getKEYWORDS($topic);
// read standard outer frame template
$bigbuf = file_get_contents($TEMPLATE_OUTER_FRAME);
$bigbuf = str_replace ('**TITLE**',"Health Topic - $topic->nlmtopic",$bigbuf);
$bigbuf = str_replace ('**KEYWORDS**',$keywords,$bigbuf);
$bigbuf = str_replace ('**DESCRIPTION**',"CCR Commons Health Topic - $topic->nlmtopic",$bigbuf);
$bigbuf = str_replace('**BODY**',$middle,$bigbuf);// find this pattern on a standard page
echo $bigbuf;

?>

