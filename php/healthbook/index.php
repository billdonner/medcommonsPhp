<?php

require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';

/**
 * Cause a HealthBook Care Team group to be created on appliance for this user if one does not already exist.
 */


function initializeUserGroup($mcid, $applianceurl, $fbid) {
	// Create group
	$initUrl = $applianceurl."/acct/hbinitialize.php?accid=$mcid&fbid=$fbid&APPCODE=".$GLOBALS['appliance_app_code'];
	$urlResult = file_get_contents($initUrl);
	$json = new Services_JSON();
	$initResult = $json->decode($urlResult);
	if($initResult && ($initResult->status=="ok")) {
		return $initResult->groupAcctId;
	}
  else
  if($initResult) 
    die("Failed to execute healthbook appliance initialization to url ".$initUrl."<br/><br/>Failed with error: ".$initResult->error);
  else
    die("Failed to execute healthbook appliance initialization to url ".$initUrl);
}


function associateUser($fbuid,$mcid,$password)
{

	$s2='&demo=1';
	$s1='status=OK&url=';
	// call an uber-service
	// figure out where this account actually is
	$remoteurl = $GLOBALS['uber_lookup']."?mcid=$mcid"; /*** TERRY FIX THIS PLEASE */
	$applianceurl = trim(file_get_contents($remoteurl));
	if (substr($applianceurl,0,strlen($s1))== $s1){
		$applianceurl = substr($applianceurl,strlen($s1));
		if (($pos = strpos($applianceurl,$s2))!==false) $applianceurl = substr($applianceurl,0,$pos);


		$applianceurl.='/';

    // Check password
    $result = file_get_contents($applianceurl."acct/ws/wsAuthenticate.php?accid=".$mcid."&pwd=".$password);
    if(preg_match('/.result.:.valid./',$result) != 1) {
      die($result);
      return false;
    }

		// okay this exists adn the password is good, so put an entry in the table
		//echo ">insert fbid $fbuid mcid $mcid appl $applianceurl<br>";

		$groupid = initializeUserGroup($mcid,$applianceurl, $fbuid);

		mysql_query("REPLACE INTO fbtab (fbid,mcid,applianceurl,targetfbid,targetmcid,groupid)
		 VALUES ('$fbuid','$mcid','$applianceurl','$fbuid','$mcid', '$groupid')")
		or die("error inserting into fbtab: ".mysql_error());

		return true;
	}
	else {
		echo "$remoteurl failed code $applianceurl<br>";
		return false;
	}
}
function insertUser($fbuid,$firstName,$lastName, $sex,$city,$state,$country,$photoUrl) {
	// this is domain where we are going to allocate facebook accounts
	$remoteurl =
	new_account_factory_appliance ()."/router/NewPatient.action?".
	"familyName=$lastName&givenName=$firstName&sex=$sex&city=$city&state=$state&country=$country".
	"&photoUrl=$photoUrl";
	//  new web service call to make the account
	//echo "creating $firstName $lastName from $city<br/>";
	$file = file_get_contents($remoteurl);
	//parse the return looking for mcid
	//echo $file;
	$m = "{status:'ok',patientMedCommonsId:'"; $ml = strlen($m);
	$pos1 = strpos($file,$m);
	$pos2 = strpos ($file,"',",$pos1);
	if ($pos2>$pos1)
	$mcid = substr($file,$pos1+$ml,16);//$pos2-$pos1-$ml);
	else
	return false;
	// figure out where this account actually is
	$applianceurl = new_account_factory_appliance ();
	//echo ">replace fbid $fbuid mcid $mcid appl $applianceurl<br>";
	$groupid = initializeUserGroup($mcid,$applianceurl, $fbuid);
	mysql_query("REPLACE INTO fbtab (fbid,mcid,applianceurl,targetfbid,targetmcid,groupid)
	VALUES ('$fbuid','$mcid','$applianceurl','$fbuid','$mcid','$groupid')") or die("error inserting into fbtab: ".mysql_error());
	return $mcid;
}
function connection_settings()
{
	$appname = $GLOBALS['healthbook_application_name'];
	$apikey = $GLOBALS['appapikey'];
	
	$connectold = <<<xxx
 <fb:explanation><fb:message>Connect to an Existing Private HealthURL</fb:message>
    <p>This feature is currently under development. You can create a new MedCommons Account for demo purposes or you can connect to the Jane H. demonstration account.</p>
    <fb:editor action="authorize_join.php" labelwidth="100">
      <fb:editor-text label="HealthURL" name="hurl" value=""/>
    </fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="connect"/>
     </fb:editor-buttonset>
  </fb:editor>
  <p>Note: You will be redirected to an authorization page to grant HealthBook access to your account.</p>
</fb:explanation>
xxx;
	//if (!isset($GLOBALS['newfeatures']))$connectold='';
	$markup = <<<XXX
<fb:if-user-has-added-app><fb:error>
      <fb:message>You Do Not Have An Existing Private HealthURL</fb:message>
      <p>You have no long term storage for your records and documents, hence the healthurl and documents links are inactive on the menu bar.  Whether you are keeping
       your own records or not, you can always be a Care Giver for your friends.</p>
      <p>If you would like to store your records and documents, choose one of the options below</p>
     </fb:error>
<fb:explanation>
      <fb:message>I Want to Create a New MedCommons Account</fb:message>
      <p>a new MedCommons account will be created and associated with your facebook account - use your facebook credentials to access healthbook</p>
      <fb:editor action='index.php'>
      <input type=hidden value=newacct name=newacct />
        <fb:editor-buttonset>
          <fb:editor-button value="create account" />
        </fb:editor-buttonset>
      </fb:editor>
 </fb:explanation>
   $connectold
    <fb:explanation>
      <fb:message>I Want to Connect to Jane Hernandez' MedCommons Account</fb:message>
      <p>if you just want to play around, you can connect to this test account; some features will be unavailable; please keep in mind that there are many other $appname users trying jane's account</p>
   <fb:editor action="index.php" labelwidth="100">
   <input type=hidden value=join name=join />
     <fb:editor-text label="mcid" name="mcid" value="1013062431111407"/>
       <fb:editor-buttonset>
          <fb:editor-button value="Connect to Jane"/>
     </fb:editor-buttonset>
  </fb:editor>
</fb:explanation>

  <fb:else> <fb:error>
      <fb:message>You must add $appname to your facebook account to maintain health records <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=posturl' ><img src='http://photos-d.ak.facebook.com/photos-ak-sctm/v43/135/6471872199/app_2_6471872199_5603.gif' />add app</a></fb:message> </fb:error></fb:else>
</fb:if-user-has-added-app>
XXX;
	return $markup;
}
function dissociateUser($user)
{
	// keep the healthbook entry around, just take out the medcommons account
	$q ="select mcid from fbtab where fbid='$user'";
	$result = mysql_query($q) or die("cant   $q ".mysql_error());
	$r = mysql_fetch_array($result);
	if ($r) {
		$mcid = $r[0];

    // remove the consent
    try {
      $u = HealthBookUser::load($user);
      $api = $u->getOAuthAPI();
      if($api) {
        $api->destroy_token($u->token);
      }
    }
    catch(Exception $e) {
      error_log("Unable to delete user user token: ".$e->getMessage());
      return false;
    }

		$q = "update fbtab set mcid='0',targetfbid='0',targetmcid='0', oauth_token = NULL, oauth_secret = NULL  where fbid='$user'";// and mcid = '$mcid'";
		$result = mysql_query($q) or die("cant   $q ".mysql_error());
		logHBEvent($user,'view',"Now viewing nothing");
		return $mcid;
	}
	return false;
}
function wantsnoacct($user)
{
	$q = "replace into  fbtab set mcid='0',targetfbid='0',targetmcid='0',fbid='$user'";// and mcid = '$mcid'";
	$result = mysql_query($q) or die("cant   $q ".mysql_error());
	logHBEvent($user,'view',"Turned off MedCommons Account");
}
// start get standard data
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->get_loggedin_user(); //require_login();
$appname = $GLOBALS['healthbook_application_name'];
$apikey = $GLOBALS['appapikey'];
$uber = $GLOBALS['uber'];
$fb_user = $facebook->user;
connect_db();


if ($user) {
	// all these cases presume we are logged on to facebook

$ret= ($facebook->api_client->users_getInfo($fb_user,array('first_name','last_name','pic_small','sex','current_location')));
if (!$ret) die ("Couldnt get info for $facebook->user also $user");
$fn = $ret[0]['first_name'];
$ln = $ret[0]['last_name'];
$pic = $ret [0] ['pic_small'];
$sex = $ret [0]['sex'];
$city = @$ret[0]['current_location']['city'];
$state = @$ret[0]['current_location']['state'];
$country = @$ret[0]['current_location']['country'];
$page = $GLOBALS['facebook_application_url'];

$u = HealthBookUser::load($user);

if (isset($_REQUEST['nojoin]']))
{

	mysql_query("REPLACE INTO fbtab (fbid,mcid,applianceurl,targetfbid,targetmcid,groupid)
		 VALUES ('$user','0','','','', '')")
	or die("error inserting into fbtab: ".mysql_error());
	// put out success message
	$dash = dashboard($user);
	$out=<<<XXX
<fb:fbml version='1.1'>
        $dash
  <fb:success>
     	<fb:message>$fn $ln is enabled as Care Giver</fb:message>
    	. To upgrade to a full account, please go to Privacy.
  </fb:success>
</fb:fbml>
XXX;
	echo $out;
	opsMailBody(  "$appname says facebook account $user $fn $ln created as a Care Giver",
	"<br>No MedCommons Account was created for facebook user $fn $ln</br>".
	"<br>You can access the user's facebook profile at http://www.facebook.com/profile.php?id=$user</br>"	);
	logHBEvent($user,'view',"Enabled as a Care Giver with no MedCommons Account");
	exit;
} else

if (isset($_REQUEST['newacct']))
{	$mcid = insertUser($fb_user,$fn,$ln,$sex,$city,$state,$country,$pic); // now calls simons stuff
// put out success message
$dash = dashboard($fb_user);
$hlf = hidden_login_frame($fb_user,$mcid);
$out=<<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:success>
     	<fb:message>A Long Term Account was created for $fn $ln</fb:message>
     	You can keep your personal records in HealthBook. You can also become a Care Giver.
   </fb:success>
  $hlf
</fb:fbml>
XXX;
echo $out;
$hurl = "$uber/$mcid";
opsMailBody(  "$appname says facebook account $user $fn $ln created medcommons account $mcid",
"<br>A MedCommons Account was created for facebook user $fn $ln</br>".
"<br>You can access the user's facebook profile at http://www.facebook.com/profile.php?id=$user</br>".
"<br>You can attempt to access the user's healthurl at $hurl</br>");
exit;
}
else
if (isset($_REQUEST['discon']))
{
	$mcid = dissociateUser($user);
	$hurl = "$uber/$mcid";
	opsMailBody(  "$appname says facebook account $user $fn $ln disconnected from  medcommons storage and services",
	"<br>Facebook user $fn $ln disconnected from medcommons account $mcid</br>".
	"<br>You can access the user's facebook profile at http://www.facebook.com/profile.php?id=$user</br>".
	"<br>You can attempt to access the user's healthurl in the disconnected account at at $hurl</br>");

republish_user_profile($user);

	$page = $GLOBALS['facebook_application_url'];
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
	echo  $markup;
	exit;
}
if (isset($_REQUEST['privacy']))
{ // wants settings
	if (($u !== false)&&($u->mcid !== false)&&($u->mcid !=='0'))
	{
		// if he wants settings, then if he has if a real account, force a disconnect
		$dash =dashboard($user);
		$mcid = $u->mcid; $appliance = $u->appliance;$hurl = $appliance.$mcid;
		$markup= <<<XXX
	<fb:fbml version='1.1'><fb:title>Privacy</fb:title>
	$dash
<fb:if-user-has-added-app>
    <fb:success>
      <fb:message>{$u->getFirstName()} {$u->getLastName()}'s Private HealthURL Storage Location is <a target='_new' href='$hurl' title='Open HealthURL in a new window' ><img src="http://www.medcommons.net/images/icon_healthURL.gif" /> $hurl</a></fb:message>

Your Facebook login and Care Givers are connected for direct access to your Private HealthURL through the HealthBook Application. If you disconnect or remove the HealthBook Application from your Facebook profile, your Private HealthURL storage account will not be affected but your Care Givers will lose access. To restore, and to view your content, you will need to have the HealthBook application and connect to <a target='_new' href='$hurl' title='Open HealthURL in a new window' ><img src="http://www.medcommons.net/images/icon_healthURL.gif" />$hurl</a>
       <fb:editor action="index.php?discon" labelwidth="100">
     <fb:editor-buttonset>  
          <fb:editor-button value="Disconnect"/>
     </fb:editor-buttonset>
          </fb:success>
          
          <fb:explanation>
             <fb:message>{$u->getFirstName()} {$u->getLastName()}'s Health URL Host is $appliance</fb:message>
  
Your HealthURL host may allow you to move account $mcid  to a different host. Please contact them directly for instructions on closing or moving an account. A directory of HealthURL hosting providers is available at MedCommons. 
     <fb:editor action="{$u->gw}/PersonalBackup" labelwidth="100">
     <input type='hidden' name='storageId' value='$mcid' />
     <input type='hidden' name='auth' value='{$u->token}' />
     <fb:editor-buttonset>  
          <fb:editor-button value="Download All Documents"/>
     </fb:editor-buttonset>

<fb:else> <fb:error>
      <fb:message>You must add $appname to your facebook account to use long-term storage       <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=discon' ><img src='http://photos-d.ak.facebook.com/photos-ak-sctm/v43/135/6471872199/app_2_6471872199_5603.gif' />add app</a></fb:message> 
      </fb:error></fb:else>
</fb:if-user-has-added-app>
    </fb:fbml>
XXX;
	echo $markup;
		exit;
	}else
	{// no medcommons account, but wants settings, invite the user to creata new account or to connect

		$dash = dashboard($user);
		$settings = connection_settings();
		$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
$settings
</fb:fbml>
XXX;
		echo $markup;
	republish_user_profile($user);
		exit;
	}
}

// case where nothing was specified, or asked for settings and wasnt logged in
if ($u !== false)
{
	require_once "home.inc.php";
	// this is the normal case for plain index.php
	// consider the likelihood that load returns false (user not found
	if($u->mcid!==false)
	{
		//**** facebook user is in the table, take him into MedCommons
		$markup = home ($user,$u->mcid,TRUE,$facebook,$u->appliance,'collaborate'); // see if we can pass anything at all

	}
	else if($u->mcid=='0')
	{
		//**** facebook user is in the table,has no medcommons account
		$markup = home ($user,$u->mcid,TRUE,$facebook,$u->appliance,'collaborate'); // see if we can pass anything at all

	}
		republish_user_profile($user);
}
else
{
	// not an app user, just give a generic topics page
	$markup = gototopics($facebook,$user);

}
}
else {
	// not logged in, just do the topics
	$markup = gototopics($facebook,$user);
}
	
echo $markup;
?>
