<?php
$REMOTE_APP="http://healthbook.medcommons.net";
$TEMPLATE_OUTER_FRAME="_topictemplate.html";
$MASTER_TOPICS_FILE="_mastertopiclist.html";
$REMOTE_WSTOPICS_SERVICE = "http://healthbook.medcommons.net/wsTopics.php";
$css = <<<XXX
<style type="text/css" >
.PublicHealthURL img {border:none;  padding-right: 8px;}
.PublicHealthURL .Time {display:inline; color:blue;}
.PublicHealthURL .TopicID {display: inline; color:green;}
.PublicHealthURL .GroupID {display: inline; color:gray;}
.PublicHealthURL .AuthorID {display:inline; color: black;}
.PublicHealthURL .Topic  {display: inline; color:green; font-size:small;}
.PublicHealthURL .HealthURL {display:inline; color: red;}
.PublicHealthURL .Comment {display:inline; color: black;}
</style>
XXX;

function xmformat($hurl,$comment,$authorid,$groupid,$topicid,$topic,$time)
{
	global $REMOTE_APP;
	if ($groupid!=0)
	$groupstuff = <<<XXX
	    <div class='GroupID'><a target='_new' title='this group on facebook' href='http://www.facebook.com/group.php?gid=$groupid' >
	    <img src='http://static.ak.facebook.com/images/icons/group.gif?48:25796' />$groupid </a></div> &nbsp;
XXX;
else $groupstuff='';
$datetime=($time+0);// force to a number of some sort
$datetime = strftime("%b %d %Y %H:%M:%S",$datetime);
return <<<XXX
<div class='PublicHealthURL'>
<h3>Public HealthURL: 
    <span class='Comment'>"$comment"  was created at <span class='Time'>$datetime</span></span> </h3>

  $groupstuff
   <span class='AuthorID'><a target='_new' title="the author's profile page on facebook"  href='http://www.facebook.com/profile.php?id=$authorid''  >
   <img src="http://static.ak.facebook.com/images/icons/friend_guy.gif?48:25796" />$authorid</a></span>
   &nbsp;
  <br/><span class='HealthURL'><a  target ='_new' href='$hurl' title='$comment' ><img src="http://www.medcommons.net/images/icon_healthURL.gif" />$hurl</a></span>
</div>	
XXX;
}
// this is the remote topics client, intended to be installed on some foreign machine ike the MedCommons website :=)

if (!isset($_REQUEST['ord'])) 
{ 
	// if not specified, show all
	
$bigbuf = file_get_contents($TEMPLATE_OUTER_FRAME);
$insides = file_get_contents ($MASTER_TOPICS_FILE);
$bigbuf = str_replace ('**TITLE**',"MedCommons Public Topic Directory ",$bigbuf);
$bigbuf = str_replace('**BODY**',$insides,$bigbuf);// find this pattern on a standard page
echo $bigbuf;
exit;
}

$ord = $_REQUEST['ord'];

$title = 'title never set error';
$str = file_get_contents("$REMOTE_WSTOPICS_SERVICE?ord=$ord");
$xml = simplexml_load_string($str);
$topics = $xml->topics;

foreach ($topics->topic as $topic)
{
	$name = $topic->name; // this doesnt change properly, not a problem when only one per page
	$nlmurl = $topic->nlmlink;
	$title = $name; //hackish
	$buf ="<div id='ContentBoxInterior' mainTitle='HealthBookTopics' >
	<h2>Public HealthURLs associated with Topic $name</h2>
	<p>This topic is derived from the ontology at Medline. You can view the base
	<a href=$nlmurl target='_new' title='open topic in new window on Medline' >medline page</a><br/>
	  You can view this on Facebook as  <span class='Topic'>
	    <a  href='http://apps.facebook.com/medcommons/topics.php?ord=$ord' title='goto $name Facebook' target='_new' >
    <img src='http://photos-a.ak.facebook.com/photos-ak-sctm/v43/228/13002635244/app_2_13002635244_1764.gif' border='0' />$name</a></span> 
    <span class='TopicID'>topic# $ord</span> &nbsp;";
	foreach ($topic->publicHealthURL as $purl)
	{
	$buf.=xmformat ( $purl->HealthURL,$purl->Comment,$purl->AuthorID,$purl->GroupID, $topic['ord'], $name,$purl->Time);	
	}
	$buf .="</div>";
}
//
// read standard outer frame template
$bigbuf = file_get_contents($TEMPLATE_OUTER_FRAME);
$bigbuf = str_replace ('**TITLE**',"MedCommons HealthBook Topic - $title",$bigbuf);
$bigbuf = str_replace('**BODY**',$css.$buf,$bigbuf);// find this pattern on a standard page
echo $bigbuf;
?>
