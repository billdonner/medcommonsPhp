<?php
$REMOTE_APP="http://healthbook.medcommons.net";
$TEMPLATE_OUTER_FRAME="_topictemplate.html";
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
<h4>A Public HealthURL: 
    <span class='Comment'>$comment  was created at <span class='Time'>$datetime</span></span> </h4>
    <span class='Topic'><a  href='$REMOTE_APP/topics.php?ord=$topicid' title='goto $topic on MedCommons HealthBook' target='_new' >
    <img src="http://photos-a.ak.facebook.com/photos-ak-sctm/v43/228/13002635244/app_2_13002635244_1764.gif" />$topic</a></span> 
    <span class='TopicID'>topic# $topicid</span> &nbsp;
  $groupstuff
   <span class='AuthorID'><a target='_new' title="the author's profile page on facebook"  href='http://www.facebook.com/profile.php?id=$authorid''  >
   <img src="http://static.ak.facebook.com/images/icons/friend_guy.gif?48:25796" />$authorid</a></span>
   &nbsp;
  <br/><span class='HealthURL'><a  target ='_new' href='$hurl' title='$comment' ><img src="http://www.medcommons.net/images/icon_healthURL.gif" />$hurl</a></span>
</div>	
XXX;
}
// this is the remote topics client, intended to be installed on some foreign machine ike the MedCommons website :=)

if (!isset($_REQUEST['ord'])) die ('usage ?ord=-topicID-');

$ord = $_REQUEST['ord'];

$title = 'title never set error';
$str = file_get_contents("$REMOTE_WSTOPICS_SERVICE?ord=$ord");
$xml = simplexml_load_string($str);
$topics = $xml->topics;

foreach ($topics->topic as $topic)
{
	$name = $topic->name; // this doesnt change properly, not a problem when only one per page
	$title = $name; //hackish
	$buf ="<div id='ContentBoxInterior' mainTitle='HealthBookTopics' >
	<h2>Public HealthURLs associated with Topic $name</h2>";
	
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
