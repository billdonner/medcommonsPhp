<?php
// careteam stuff
require_once 'healthbook.inc.php';
function  careteam_notify_list ($user,$facebook)
{ // return a string which is an array delimited list of facebook ids
$counter = 0; $outstr=array();
$q = "select * from  careteams c, fbtab f  where c.mcid = f.mcid and f.fbid = '$user' ";
$result = mysql_query($q) or die("cant  $q ".mysql_error());
while($u=mysql_fetch_object($result))
{


	$outstr[] = $u->giverfbid;

	$counter++;
}

mysql_free_result($result);
return $outstr;
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
$dash = dashboard($user,false);
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
$sendmail = $facebook->api_client->notifications_send($uid, $notification,'user_to_user');
$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
<fb:explanation>
<fb:message>$appname -- Your Care Team Was Notified</fb:message>
<p>They each received the message $subject $body</p>
<p>Your friends will be receiving both emails and facebook notifications</p>
<p>$appname will never add anyone to your CareTeam without mutual consent.</p>
       <fb:editor action="index.php" labelwidth="100">
       <fb:editor-buttonset>
       <fb:editor-button value="OK"/>
     </fb:editor-buttonset>
  </fb:editor>
 </fb:explanation>
</fb:fbml>
XXX;


}
else
if (isset($_REQUEST['wallmcid']))
{ // wall handler completion
$wallmcid = $_REQUEST['wallmcid'];
$body = $_REQUEST['body'];
if (isset($_REQUEST['info'])) $severity=1; else $severity=0;
$rbody = mysql_escape_string($_REQUEST['body']);
$now = time();
$q = "REPLACE INTO carewalls set wallmcid = '$wallmcid', severity='$severity', authorfbid='$user',time='$now',msg='$rbody' ";
mysql_query($q) or die ("Cant $q");

/* dont tell anyone about this
 $alink = $GLOBALS['facebook_application_url'];
 $feed_title = "<fb:userlink uid=$user shownetwork=false />  wrote to <fb:name uid=$wallmcid possessive=true /> carewall: $body ";
 $feed_body = "Check out <a href=$alink >".$GLOBALS['healthbook_application_name']."</a>,  where <fb:name uid='$wallmcid' firstnameonly=trueuseyou=false possessive=false /> banks medical records</a>";
 logMiniHBEvent($wallmcid,'carewalls',$feed_title,$feed_body);

 if (isset($_REQUEST['info'])) if ($user==$wallmcid) publish_info($user); // publish about self
 */
//republish_user_profile($wallmcid); // push this out ony if asked for
$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
} else if (isset($_REQUEST['removepost']))
{ // wall handler completion
$dbrecid= $_REQUEST['id'];
$time=$_REQUEST['removepost'];
$q = "Delete from carewalls where id='$dbrecid' and time='$time'  ";
mysql_query($q) or die ("Cant $q");

//	if ($user==$wallmcid) publish_info($user); // publish about self

//republish_user_profile($wallmcid); // push this out ony if asked for
$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}
else
if (isset($_REQUEST['refmcid'])){


	// promote joined status of careteam, also has op=j set *** needs fixing k
	$ref= $_REQUEST['refmcid'];
	$family = $_REQUEST['family'];
	$q = "REPLACE INTO careteams set mcid = '$ref', giverfbid='$user',giverrole='4' ";
	mysql_query($q) or die ("Cant $q");

	$q = "select * from   fbtab  where fbid='$family'  ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r1 = mysql_fetch_object($result);
	if ($r1) {
		
		$ret= ($facebook->api_client->users_getInfo($user,array('first_name','last_name','pic_small','sex'))); //sex
if (!$ret) {
	logHBEvent($user,'nouser',"Couldnt call users_getInfo on $user");
	die ("Couldnt get info for  $user");
}

$fn = mysql_real_escape_string($ret[0]['first_name']);
$ln = mysql_real_escape_string($ret[0]['last_name']);
$ps = mysql_real_escape_string($ret [0] ['pic_small']);
$sx = mysql_real_escape_string($ret [0]['sex']);


			$q = "replace into fbtab set  mcid='$ref', sponsorfbid='$r1->sponsorfbid', targetmcid='$r1->targetmcid',familyfbid='$family' ,fbid='$user',
			firstname='$fn', lastname='$ln', sex='$sx',
			photoUrl='$ps' ,oauth_token='$r1->oauth_token',oauth_secret='$r1->oauth_secret',applianceurl='$r1->applianceurl',gw='$r1->gw'
 "; // ** remove targetfbid
			mysql_query($q) or die("Cant $q ".mysql_error());

	}

	$dash = dashboard($user,false);
	$markup = <<<XXX
	<fb:fbml version='1.1'>
	$dash
	<fb:success>
	<fb:message>You have joined the Family Careteam of <fb:name uid=$family> mcid $ref </fb:message>
 	<p>You can now help care for your family  </p>
       <fb:editor action="familycareteam.php" labelwidth="100">
     <fb:editor-buttonset>
          <fb:editor-button value="home"/>
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
		/*	$alink = $GLOBALS['facebook_application_url'];
		 $feed_title = '<fb:userlink uid="'.$fbid.'" shownetwork="false"/>has been invited to  join the care team of  <fb:userlink uid="'.$user.'" shownetwork="false"/>';
		 $feed_body ="Check out <a href='.$alink.'>".$GLOBALS['healthbook_application_name'].
		 "</a>, where <fb:name uid=$user firstnameonly=true possessive=false /> and
		 <fb:name uid=$fbid firstnameonlytrue possessive=false /> bank medical records</a>.";
		 logMiniHBEvent($user,'carewalls',$feed_title,$feed_body);
		 */
		//echo $q."<br/>";
	}
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}

/*
 * else
 if ($op == 'i')
 {
 // get my care team and be sure not to ask them again
 $dash = dashboard($user,'invite');
 $arFriends = "";
 $q = "SELECT giverfbid from careteams c, fbtab f where f.fbid = '$user' and f.mcid = c.fmcid and (c.giverrole='1' or c.giverrole='4')"; //bill = dont reinvite people with outstanding invites
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
 $sNextUrl =$GLOBALS['facebook_application_url']."/index.php?ref=fbinvite";
 // note: exclude_ids='' seemed to cause some problems, so avoid that
 $excludeIds = ($arFriends != "") ? "exclude_ids='$arFriends'" : "";
 //  Build your invite text
 $invfbml = <<<FBML
 I've been organizing my health using  $appname on Facebook.  I think you will find it useful and always private.
 After you join, you can set up a Care Giving relationship with your friends and family.
 <fb:req-choice url='$sNextUrl' label="Join $appname" />
 FBML;
 $invfbml = htmlentities($invfbml);
 $markup = <<<XXX
 <fb:fbml version='1.1'>
 $dash
 <fb:explanation>
 <fb:message>Invite Your Friends to $appname</fb:message>
 Note: this is an invitation for your friends to add the $appname application. Once added, they can potentially help you with your care.
 <br/>
 If you are keeping your health records on $appname you can <a href='home.php?o=t' title='My Care Team lets you add and remove members' >invite your Facebook friends</a> to your Care Team.
 <fb:request-form action="index.php?ref=invitemc" method="POST" invite="false" type="Join $appname" content="$invfbml">
 <fb:multi-friend-selector max="20" actiontext="Invite your friends to join $appname" 	showborder="true" rows="5" $excludeIds>
 </fb:request-form>
 <p>$appname will never add anyone to your CareTeam without their explicit permission.</p>
 </fb:explanation>
 </fb:fbml>
 XXX;

 logHBEvent($user,'invite',"o=i invite to $sNextUrl");

 }
 */
else if ($op=='h') //healthbook invites for friends // **************** not quite right for new rewrite around mcid based careteam
{
	$dash = dashboard($user,false);
	$q = "SELECT * from fbtab where fbid = '$user' "; //bill
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	$f = mysql_fetch_object($result);
	if (!$f || $f->mcid=='0')
	{
		$markup=<<<XXX
		<fb:fbml version='1.1'>
		$dash
		<fb:error>
		<fb:message>Please Create a $appname Account Before Inviting a Care Team</fb:message>
		<p>You must have a MedCommons Account to create a Care Tea,</p>
		<p>Please go to the <a href=settings.php >settings page </a> to create a $appname  Account.</p>
  </fb:error>
</fb:fbml>
XXX;
		echo $markup;
		exit;
	}

	$dash = dashboard($user,false);


	$arFriends = "";
	$q = "SELECT giverfbid from careteams where mcid = '$f->mcid' and (giverrole='1' or giverrole='4')"; //bill = dont reinvite people with outstanding invites
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ( $arFriends != "" )
		$arFriends .= ",";
		$arFriends .= $u->giverfbid;
	}
	mysql_free_result($result);

	// Construct a next url for referrals $sNextUrl = urlencode("&refuid=".$user);
	$sNextUrl =$GLOBALS['facebook_application_url']."ct.php?family=$f->familyfbid&refmcid=".$f->mcid;

	//  Build your invite text
	$invfbml = <<<FBML
	Please help care for our family by joining our MedCommons Family CareTeam.
	By accepting this invitation you will be able to share medical records and help other family members.  Thank you

	<fb:req-choice url="$sNextUrl" label="Please Join My Family CareTeam" />
FBML;
	$invfbml = htmlentities($invfbml);
	$markup = <<<XXX
	<fb:fbml version='1.1'><fb:title>Invite Friends to Join Family CareTeam</fb:title>
	$dash
	<fb:explanation>
	<fb:message>Family CareTeam Invitations</fb:message>
	<fb:request-form
	action="index.php?ref=invitect"
	method="POST"
	invite="true"
	type="Family CareTeam"
	content="$invfbml">
	<fb:multi-friend-selector max="20" actiontext="Invite friends to join your FamilyCareTeam"
	showborder="true" rows="5" exclude_ids="$arFriends">
	</fb:request-form>
	<p>$appname will never add anyone to your Family CareTeam without mutual consent.</p>
  </fb:explanation>
</fb:fbml>
XXX;
	logHBEvent($user,'invite',"o=h invite to $sNextUrl");
}
/*
 else if ($op=='a') //add
 {
 $mcid = $_REQUEST['id'];
 $giverfbid = $_REQUEST['gid'];
 $q = "REPLACE INTO careteams set mcid = '$mcid', giverfbid='$giverfbid',giverrole='4' ";
 mysql_query($q) or die ("Cant $q");

 logHBEvent($user,'careteamadd',"fbid $fbid giver $fiverfbid");
 $alink = $GLOBALS['facebook_application_url'];
 $feed_title = '<fb:userlink uid="'.$giverfbid.'" shownetwork="false"/>has joined the care team of  <fb:userlink uid="'.$fbid.'" shownetwork="false"/>';
 $feed_body = "Check out <a href=$alink >".$GLOBALS['healthbook_application_name']."</a>, where <fb:name uid=$giverfbid
 firstnameonly=true possessive=false /> and
 <fb:name uid=$fbid  firstnameonly=true possessive=false/> bank medical records</a>.";
 logMiniHBEvent($user,'carewalls',$feed_title,$feed_body);
 logMiniHBEvent($giverfbid,'carewalls',$feed_title,$feed_body);

 $markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
 }
 */

else if ($op=='w') //write to wall
{
	$q = "Select * from fbtab where fbid='$user'";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r = mysql_fetch_object($result);
	$wallmcid = $r->targetmcid;
	$markup = <<<XXX
	<fb:fbml version='1.1'>
	$dash
	<fb:explanation>
	<fb:message>$appname -- Write to CareWall</fb:message>
	<p>Normally, only members of your team can see this wall.  But if you want to push a particular message out to your Info Tab for all your friends to see, hit the "Write to My Info Profile Tab" button.
	Remember, you can use the direct communications facility within MedCommons to transmit clinical information to providers not on $appname
	</p>
	<fb:editor action="ct.php" labelwidth="100">
	<fb:editor-custom label="message">
	<textarea rows=5 cols=50 name='body'></textarea>
	<input type=hidden name=wallmcid value=$wallmcid />
	</fb:editor-custom>
	<fb:editor-buttonset>
	<fb:editor-button name=wall value="Write to CareWall"/>
	<fb:editor-button name=info value="Write to My Info Profile Tab"/>
	<fb:editor-cancel />
	</fb:editor-buttonset>
	</fb:editor>
	<p>$appname will never allow anyone other than a CareTeam member to view a CareWall.</p>
  </fb:explanation>
</fb:fbml>
XXX;
}
else if ($op=='n') //notify
{  //
$dash = dashboard($user,false);
$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
<fb:explanation>
<fb:message>Send a Message to All Family CareTeam Members</fb:message>
<p>The message will be sent via facebook to all Care Team members.</p>
<fb:editor action="ct.php" labelwidth="100">
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

logHBEvent($user,'careteamnotify',"poststo non-existant ctnofiy.php");
}
else if ($op=='r') // REMOVE
{
	$fbid  = $_REQUEST['id'];
	$giverfbid = $_REQUEST['gid'];
	$q = "Select * from fbtab where fbid = '$fbid' ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r = mysql_fetch_object($result);
	if ($r) {
		$q = "DELETE from careteams where mcid = '$r->mcid' and giverfbid='$giverfbid'";
		mysql_query($q) or die("Cant $q ".mysql_error());
		/*
		 set the caregiver so he is no longer viewing the target's records
		 */


		$q = "replace into fbtab set  mcid='0', sponsorfbid='0', targetmcid='0',familyfbid='$user' ,fbid='$giverfbid',
		oauth_token='',oauth_secret='',applianceurl='',gw=''
	  "; // ** remove targetfbid
		mysql_query($q) or die("Cant $q ".mysql_error());
		logHBEvent($user,'careteamremove',"removed giver $giverfbid from $fbid care team");
		$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
	}
}
else if ($op=='b') // REMOVE this user from all care teams he is on
{

	$q = "Select * from fbtab where fbid = '$user' ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r = mysql_fetch_object($result);
	if ($r) {
		$q = "DELETE from careteams where giverfbid='$user'";
		mysql_query($q) or die("Cant $q ".mysql_error());
		/*
		 set the caregiver so he is no longer viewing the target's records
		 */


		$q = "replace into fbtab set  mcid='0', sponsorfbid='0', targetmcid='0',familyfbid='$user' ,fbid='$user',
		oauth_token='',oauth_secret='',applianceurl='',gw=''
	  "; // ** remove targetfbid
		mysql_query($q) or die("Cant $q ".mysql_error());
		logHBEvent($user,'careteamremove',"removed user $user from all care teams");
		$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
	}
}
else $markup = "Unknown op code $op";

echo $markup;
?>
