<?php
// careteam stuff
require 'healthbook.inc.php';

function careteam_dashboard ($user, $kind)
{
	$top = dashboard($user);
	$bottom = <<<XXX
<fb:tabs>
 <fb:tab_item href='home.php' title='overview' />
      <fb:tab_item href='ct.php?o=h' title='careteam invite' />
      <fb:tab_item href='ct.php?o=n' title='notify team' />
 
 </fb:tabs>
XXX;
	$needle = "title='$kind'";
	$ln = strlen($needle);
	$pos = strpos ($bottom,$needle);
	if ($pos!==false)
	{  // add selected item if we have a match
		$bottom = substr($bottom,0,$pos)." selected='true' ".
		substr ($bottom, $pos);
	}
	return $top.$bottom;
}
//**start here
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
connect_db();
$page = $GLOBALS['facebook_application_url'];
list($mcid,$appliance) = fmcid($user);
if ($mcid===false) die ("Internal error, fb user $user has no mcid");
$appname = $GLOBALS['healthbook_application_name'];
$dash = careteam_dashboard($user,'notify team');
if (isset($_REQUEST['o'])) $op =  $_REQUEST['o']; else $op='';
if (isset($_REQUEST['send']))  //SEND THE EMAIL

{	// send
	// send a real email
	$subject = $_REQUEST['subject'];
	$body = $_REQUEST['body'];
	$notification = " on $appname says $subject $body";
	$emailSubject = "<fb:notif-subject>$subject</fb:notif-subject>";
	$email = "<html>$body</html>".$emailSubject;
	$uid = careteam_notify_list($user,$facebook);
	$sendmail = $facebook->api_client->notifications_send($uid, $notification, $email);
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:explanation>
    <fb:message>$appname -- Your Team Was Notified</fb:message> 
    <p>They each received the message $subject $body</p>
      <p>Your  will be receiving both emails and facebook notifications</p> 
      <p>$appname will never add anyone to your CareTeam without mutual consent.</p>  
       <fb:editor action="home.php" labelwidth="100">
       <fb:editor-buttonset>
       <fb:editor-button value="OK"/>
     </fb:editor-buttonset>
  </fb:editor>
 </fb:explanation>
</fb:fbml>
XXX;


}
else
if (isset($_REQUEST['wallfbid']))
{ // wall handler completion
	$wallfbid = $_REQUEST['wallfbid'];
	$body = $_REQUEST['body'];
	$rbody = mysql_escape_string($_REQUEST['body']);
	$now = time();
	$q = "REPLACE INTO carewalls set wallfbid = '$wallfbid', authorfbid='$user',time='$now',msg='$rbody' ";
	mysql_query($q) or die ("Cant $q");
	$alink = $GLOBALS['facebook_application_url'];
	$feed_title = "<fb:userlink uid=$user shownetwork=false />  wrote to <fb:name uid=$wallfbid possessive=true /> carewall: $body ";
	$feed_body = "Check out <a href=$alink >".$GLOBALS['healthbook_application_name']."</a>,  where <fb:name uid='$wallfbid' firstnameonly=trueuseyou=false possessive=false /> banks medical records</a>";
	logMiniHBEvent($wallfbid,'carewalls',$feed_title,$feed_body);
	republish_user_profile($wallfbid);
	/*
	if ($user!=$wallfbid){
	logMiniHBEvent($user,'carewalls',$feed_title,$feed_body);
	republish_user_profile($user);
	}*/
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
} else

if (isset($_REQUEST['refuid'])){
	// promote joined status of careteam, also has op=j set
	$ref= $_REQUEST['refuid'];
	$q = "REPLACE INTO careteams set fbid = '$ref', giverfbid='$user',giverrole='4' ";
	mysql_query($q) or die ("Cant $q");
	$alink = $GLOBALS['facebook_application_url'];
	$feed_title = '<fb:userlink uid=$user shownetwork="false"/>  joined the care team of  <fb:userlink uid="'.$ref.'" shownetwork="false"/>';
	$feed_body = "Check out <a href=$alink >".$GLOBALS['healthbook_application_name']."</a>, where
	<fb:name uid=$user f irstnameonly='true' possessive='false' /> and
	<fb:name uid=$ref  firstnameonly='true' possessive='false' /> bank medical records</a>.";
	logMiniHBEvent($user,'carewalls',$feed_title,$feed_body);


	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:success>
    <fb:message>You Have Joined the Careteam</fb:message>
 	<p>You can now help your friend</p>
       <fb:editor action="home.php" labelwidth="100">
     <fb:editor-buttonset>
          <fb:editor-button value="continue"/>
     </fb:editor-buttonset>
</fb:editor>
  </fb:success>
</fb:fbml>
XXX;
}
else
if (isset($_REQUEST['ids']))
{
	// silently fix up careteam
	$ids = $_REQUEST['ids'];
	//echo "ctcthandler: ".count($ids);
	// mstk each id as 'invited'

	for ($i=0; $i<count($ids); $i++)
	{
		$fbid = $ids[$i];
		//set this user up as invited
		$q = "REPLACE INTO careteams set fbid = '$user', giverfbid='$fbid',giverrole='1' ";
		mysql_query($q) or die ("Cant $q");
		$alink = $GLOBALS['facebook_application_url'];
		$feed_title = '<fb:userlink uid="'.$fbid.'" shownetwork="false"/>has been invited to  join the care team of  <fb:userlink uid="'.$user.'" shownetwork="false"/>';
		$feed_body ="Check out <a href='.$alink.'>".$GLOBALS['healthbook_application_name'].
		"</a>, where <fb:name uid=$user firstnameonly=true possessive=false /> and
	<fb:name uid=$fbid firstnameonlytrue possessive=false /> bank medical records</a>.";
		logMiniHBEvent($user,'carewalls',$feed_title,$feed_body);

		//echo $q."<br/>";
	}
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}
else
if ($op == 'i')
{
	// get my care team and be sure not to ask them again
	$dash = dashboard($user,'invite');
	$arFriends = "";
	$q = "SELECT giverfbid from careteams where fbid = '$user' and (giverrole='1' or giverrole='4')"; //bill = dont reinvite people with outstanding invites
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ( $arFriends != "" )
		$arFriends .= ",";
		$arFriends .= $u->giverfbid;
	}
	mysql_free_result($result);

	//  Get list of friends who  have this app installed... they should be excluded as well
	$rs = $facebook->api_client->fql_query("SELECT uid FROM user WHERE has_added_app=1 and uid IN (SELECT uid2 FROM friend WHERE uid1 = $user)");

	//  Build an delimited list of users...
	if ($rs)
	{	for ( $i = 0; $i < count($rs); $i++ )	{	if ( $arFriends != "" )$arFriends .= ",";	$arFriends .= $rs[$i]["uid"];	}
	}

	//  Construct a next url for referrals
	$sNextUrl =$GLOBALS['facebook_application_url']."/index.php";
	// note: exclude_ids='' seemed to cause some problems, so avoid that
	$excludeIds = ($arFriends != "") ? "exclude_ids='$arFriends'" : "";
	//  Build your invite text
	$invfbml = <<<FBML
I've been organizing my health using  $appname from MedCommons.  I think you will find it useful and always private. 
<fb:req-choice url='$sNextUrl' label="Join $appname" />
FBML;
	$invfbml = htmlentities($invfbml);
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:explanation>
    <fb:message>Invite Your Friends to $appname</fb:message>
    This is not an invitation for a friend to join your Care Team, 
    it is only an invitation for your friend to add the $appname application. 
    <br/>
     If you are keeping your health records on $appname you can 
     <a href='home.php?o=t' title='My Care Team lets you add and remove members' >invite a friend</a> to your Care Team.
<fb:request-form 
action="topics.php" 
method="POST" 
invite="false" 
type="CareTeam" 
content="$invfbml">
	<fb:multi-friend-selector max="20" actiontext="Invite your friends to join $appname" 
	showborder="true" rows="5" $excludeIds>
</fb:request-form>
      <p>$appname will never add anyone to your CareTeam without their explicit permission.</p>  
  </fb:explanation>
</fb:fbml>
XXX;

}
else if ($op=='h') //healthbook invites for friends
{
	// get my care team and be sure not to ask them again
	$dash = careteam_dashboard($user,'careteam invite');
	$arFriends = "";
	$q = "SELECT giverfbid from careteams where fbid = '$user' and (giverrole='1' or giverrole='4')"; //bill = dont reinvite people with outstanding invites
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ( $arFriends != "" )
		$arFriends .= ",";
		$arFriends .= $u->giverfbid;
	}
	mysql_free_result($result);

	// Construct a next url for referrals $sNextUrl = urlencode("&refuid=".$user);
	$sNextUrl =$GLOBALS['facebook_application_url']."/ct.php?o=j&refuid=".$user;
	//  Build your invite text
	$invfbml = <<<FBML
Please help me care for my health by joining $appname  and my CareTeam.  
<fb:req-choice url="$sNextUrl" label="Join $appname" />
FBML;
	$invfbml = htmlentities($invfbml);
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Invite</fb:title>
$dash
  <fb:explanation>
    <fb:message>$appname -- Invite Your Friends to $appname and to Your CareTeam</fb:message>
<fb:request-form 
action="topics.php" 
method="POST" 
invite="false" 
type="$appname" 
content="$invfbml">
	<fb:multi-friend-selector max="20" actiontext="Invite your friends  to join $appname and your CareTeam" 
	showborder="true" rows="5" exclude_ids="$arFriends">
</fb:request-form>
      <p>$appname will never add anyone to your CareTeam without mutual consent.</p>  
  </fb:explanation>
</fb:fbml>
XXX;
}

else if ($op=='a') //add
{
	$fbid = $_REQUEST['id'];
	$giverfbid = $_REQUEST['gid'];
	$q = "REPLACE INTO careteams set fbid = '$fbid', giverfbid='$giverfbid',giverrole='4' ";
	mysql_query($q) or die ("Cant $q");

	$alink = $GLOBALS['facebook_application_url'];
	$feed_title = '<fb:userlink uid="'.$giverfbid.'" shownetwork="false"/>has joined the care team of  <fb:userlink uid="'.$fbid.'" shownetwork="false"/>';
	$feed_body = "Check out <a href=$alink >".$GLOBALS['healthbook_application_name']."</a>, where <fb:name uid=$giverfbid
	 firstnameonly=true possessive=false /> and
	<fb:name uid=$fbid  firstnameonly=true possessive=false/> bank medical records</a>.";
	logMiniHBEvent($user,'carewalls',$feed_title,$feed_body);
	logMiniHBEvent($giverfbid,'carewalls',$feed_title,$feed_body);

	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}

else if ($op=='w') //write to wall
{
	$q = "Select * from fbtab where fbid='$user'";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r = mysql_fetch_object($result);
	$wallfbid = $r->targetfbid;
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:explanation>
    <fb:message>$appname -- Write to CareWall</fb:message> 
      <p>Only members of your team can see this wall. Remember, you can use the direct communications facility within MedCommons to transmit clinical information to providers not on $appname  
      </p>
     <fb:editor action="ct.php" labelwidth="100">
         <fb:editor-custom label="message">
          <textarea rows=5 cols=50 name='body'></textarea>
          <input type=hidden name=wallfbid value=$wallfbid />
       </fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="Write to CareWall"/>
     </fb:editor-buttonset>
 </fb:editor>
       <p>$appname will never allow anyone other than a CareTeam member to view a CareWall.</p>  
  </fb:explanation>
</fb:fbml>
XXX;
}
else if ($op=='n') //notify
{  //
	$dash = careteam_dashboard($user,'notify team');
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:explanation>
    <fb:message>$appname -- Notify CareTeam Members</fb:message> 
      <p>Use this page to communicate directly with all members of the team. 
      You can use the direct communications facility within MedCommons to transmit clinical information to providers not on $appname  
      </p>
      <fb:editor action="ctnotify.php" labelwidth="100">
     <fb:editor-text name="subject" label="subject" value=""/>
     <input type=hidden name=send value=send>
      <fb:editor-custom label="message">
          <textarea rows=5 cols=50 name='body'></textarea>
       </fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="Notify $appname CareTeam"/>
     </fb:editor-buttonset>
 </fb:editor>
       <p>$appname will never add anyone to any CareTeam without mutual consent.</p>  
  </fb:explanation>
</fb:fbml>
XXX;
}
else if ($op=='r') // REMOVE
{
	$fbid = $_REQUEST['id'];
	$giverfbid = $_REQUEST['gid'];
	$q = "DELETE from careteams where fbid='$fbid' and giverfbid='$giverfbid'";
	mysql_query($q) or die("Cant $q ".mysql_error);
	/*
	set the caregiver so he is no longer viewing the target's records
	*/
	$q = "UPDATE fbtab set targetfbid='0', targetmcid='0' where fbid='$giverfbid'";
	mysql_query($q) or die("Cant $q ".mysql_error);

	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}
else $markup = "Unknown op code $op";

echo $markup;
?>
