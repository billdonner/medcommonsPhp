<?php

require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';

function insertUser($fbuid,$firstName,$lastName, $sex,$city,$state,$country,$photoUrl,$key,$product) {

	global $oauth_consumer_key;

	// this is domain where we are going to allocate facebook accounts
	$applianceUrl = new_account_factory_appliance();
	$remoteurl =$applianceUrl."/router/NewPatient.action?".
	"familyName=$lastName&givenName=".urlencode($firstName).
	"&sex=".urlencode($sex).
	"&city=".urlencode($city).
	"&state=".urlencode($state).
	"&country=".urlencode($country).
	"&auth=".urlencode($oauth_consumer_key).
	"&oauth_consumer_key=".urlencode($oauth_consumer_key).
	"&photoUrl=".urlencode($photoUrl).
	"&activationKey=".urlencode($key).
	"&activationProductCode=".urlencode($product);

	error_log("creating new patient with url ".$remoteurl);

	// if a support group is defined for this app, set it as the sponor so that
	// support team has access to this new patient's account
	if(isset($GLOBALS['new_account_support_group_mcid']))
	$remoteurl .= "&sponsorAccountId=".urlencode($GLOBALS['new_account_support_group_mcid']);

	//  new web service call to make the account
	//echo "creating $firstName $lastName from $city<br/>";
	$result = file_get_contents($remoteurl);

	//parse the return looking for mcid
	$json = new Services_JSON();
	$patient_info = $json->decode($result);

	if(!$patient_info) {
		error_log("Failed to create new storage account - unable to decode patient info: $result");
		return false;
	}

	if($patient_info->status !== "ok") {
		error_log("Failed to create new storage account - NewAccount service returned failure status with error: ".$patient_info->error);
		return false;
	}

	$mcid = $patient_info->patientMedCommonsId;
	$auth = $patient_info->auth;
	$secret = $patient_info->secret;
	
	$appname = $GLOBALS['healthbook_application_name'];
	$firstName = mysql_real_escape_string($firstName);
	$lastName = mysql_real_escape_string($lastName);
	$now = time();
//	dbg("Received auth $auth and secret $secret for new patient $mcid");
	logHBEvent($fbuid ,'added',"Self added user fbid $fbuid mcid $mcid $firstName $lastName");
	
	//  now must carefully add this
	mysql_query("REPLACE INTO fbtab (fbid,mcid,applianceurl,sponsorfbid,familyfbid,targetmcid,groupid,oauth_token,oauth_secret,firstname,lastname,sex,photoURL)
	VALUES ('$fbuid','$mcid','$applianceUrl','$fbuid','$fbuid','$mcid',NULL,'$auth','$secret','$firstName','$lastName','$sex','$photoUrl')") or die("error inserting into fbtab: ".mysql_error());
	$q = "REPLACE INTO careteams set mcid = '$mcid', giverfbid='$fbuid',giverrole='4' ";
	mysql_query($q) or die ("Cant $q");
	$q = "REPLACE INTO carewalls set wallmcid = '$mcid', authorfbid='$fbuid',msg='I created this account for myself',time=$now ";
	mysql_query($q) or die ("Cant $q");
	
	// if there is a record lying around from before we added the key then lets remove it
	
	mysql_query("Delete From fbtab where (mcid='0') and (fbid='$fbuid') ");
	
	return $mcid;
}
function disconnection_settings($user,$u)
{
	// if he wants settings, then if he has if a real account, force a disconnect
	$appname = $GLOBALS['healthbook_application_name'];
	$apikey = $GLOBALS['appapikey'];
	publish_info($user);

  // ssadedin: removed this because I couldn't make it work - it always displays the text but the
  // button never displays and I see nothing on the Info tab either way. 
  // This probably means I don't understand it. 
  /*
<fb:if-section-not-added section="info">
    <fb:success>
      <fb:message>Add My Care Team and Care Giving to my Info Tab 
        <div style='margin: 10px 0px'><fb:add-section-button section="info" /></div>
     </fb:message>
     <small>All of your friends will see this information, You can always remove this directly from the Info Tab.</small>
    </fb:success>
</fb:if-section-not-added>	
   */

	$addprofile = <<<XXX
<fb:if-section-not-added section="profile">
    <fb:success>
      <fb:message>Add My Info to My Profile Box 
          <div style='margin: 10px 0px'><fb:add-section-button section="profile" /></div>
       </fb:message>
       <small>All of your friends will see this information, You can always remove this directly .</small>
      </fb:success>
</fb:if-section-not-added>
XXX;

  $mcid = $u->mcid; 
  $appliance = $u->appliance;
  $hurl = rtrim($appliance,'/').'/'.$mcid;
	$markup= <<<XXX

<fb:if-is-app-user>
    <fb:success>
      <fb:message>{$u->getFirstName()} {$u->getLastName()}'s Private HealthURL Storage Location is <br/>
      <div style='text-align: left; margin: 10px 0px;'>
        <a target='_new' href='$hurl' title='Open HealthURL in a new window' ><img src="http://www.medcommons.net/images/icon_healthURL.gif" /> $hurl</a>
      </div>
      </fb:message>

      Your Facebook login and Care Givers are connected for direct access to your 
      Private HealthURL through the HealthBook Application. If you disconnect or 
      remove the HealthBook Application from your Facebook profile, your Private 
      HealthURL storage account will not be affected but your Care Givers will lose 
      access. To restore, and to view your content, you will need to have the 
      HealthBook application and connect to <a target='_new' href='$hurl' title='Open HealthURL in a new window' >
      <img src="http://www.medcommons.net/images/icon_healthURL.gif" />$hurl</a>

     <fb:dialog id="my_dialog" cancel_button=1>
      <fb:dialog-title>Disconnect from My MedCommons Account</fb:dialog-title>	
      <fb:dialog-content><form id="my_form">Do you really want to disconnect from your MedCommons Account?</form></fb:dialog-content>
      <fb:dialog-button type="button" value="Yes" href="settings.php?discon" /> 
    </fb:dialog>
    <div style='text-align: center; margin: 10px 0px;'>
      <a href="#" clicktoshowdialog="my_dialog"><button class='confirmbuttonstyle'>Disconnect</button></a>
    </div>
  </fb:success>

$addprofile

  <fb:success>
             <fb:message>Login to {$u->getFirstName()} {$u->getLastName()}'s Health URL Host</fb:message>
If you are the patient, or Custodian, you can login directly to the MedCommons Account on $appliance
       <fb:editor action="$appliance/acct/login.php?mcid=$mcid" labelwidth="100">
     <fb:editor-buttonset>  
          <fb:editor-button value='Sign In Directly to Your HealthURL' />
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
 </fb:explanation>
<fb:else> <fb:error>
      <fb:message>You must add $appname to your facebook account to use long-term storage       <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=discon' ><img src='http://photos-d.ak.facebook.com/photos-ak-sctm/v43/135/6471872199/app_2_6471872199_5603.gif' />add app</a></fb:message> 
      </fb:error></fb:else>
</fb:if-is-app-user>
XXX;
	return $markup;
}

function noadmin_settings($fbid)
{

	$markup = <<<xxx
 <fb:success><fb:message>You are a member of a Family Care Team</fb:message>
    <p>You can not create a personal medcommons account and remain a member of this teamn created by <fb:name uid=$fbid/></p>
   <p>You should remove yourself from the team and then try again</p>
</fb:success>
xxx;

	return $markup;
}

function connection_settings()
{
	$appname = $GLOBALS['healthbook_application_name'];
	$apikey = $GLOBALS['appapikey'];
  $newAcctAppliance=rtrim($GLOBALS['new_account_appliance'],'/');
	$connectold = <<<xxx
 <fb:explanation><fb:message>Connect to an Existing Private HealthURL</fb:message>
    <p>You can connect to an existing MedCommons Account  or you can connect to the Jane H. demonstration account.</p>
    <fb:editor action="authorize_join.php" labelwidth="100">
      <fb:editor-text label="HealthURL" name="hurl" value=""/>
    </fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="connect"/>
     </fb:editor-buttonset>
  </fb:editor>
  <p>Note: You will be redirected to an authorization page to grant $appname access to your account. If you want to connect to a demo account you can use
$newAcctAppliance/1013062431111407
  </p>
</fb:explanation>
xxx;

	require_once "noacct_blurb.inc.php";
	$markup = <<<XXX
<fb:if-is-app-user>
      $noacct_blurb
<fb:explanation>
      <fb:message>I Want to Create a New MedCommons Account</fb:message>
      <p>A new MedCommons account will be created and associated with your facebook account - use your facebook credentials to access healthbook</p>
      <fb:editor action='settings.php'>
      <input type=hidden value=newacct name=newacct />
        <fb:editor-buttonset>
          <fb:editor-button value="create account" />
        </fb:editor-buttonset>
      </fb:editor>
      <p>Note: you will be forwarded to Amazon Payments to purchase a MedCommons subscription.</p>
 </fb:explanation>
   $connectold
    <fb:else> <fb:error>
      <fb:message>You must add $appname to your facebook account to maintain health records <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=posturl' ><img src='http://photos-d.ak.facebook.com/photos-ak-sctm/v43/135/6471872199/app_2_6471872199_5603.gif' />add app</a></fb:message> </fb:error></fb:else>
</fb:if-is-app-user>
XXX;
	return $markup;
}

function dissociateUser($user)
{
	// keep the healthbook entry around, just take out the medcommons account
	// also blow away the careteam if any
	$q ="select mcid from fbtab where fbid='$user'";
	$result = mysql_query($q) or die("cant   $q ".mysql_error());
	$r = mysql_fetch_array($result);
	$success = true;
	if ($r) {
		$mcid = $r[0];
		// also blow away the careteam but dont blow away caregiving  until we remove the app
		$q ="delete from careteams  where mcid='$mcid' "; // or giverfbid='$user' ";
		$result = mysql_query($q) or die("cant   $q ".mysql_error());


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
			$success = false;
		}

		$q = "update fbtab set mcid='0',sponsorfbid='0',targetmcid='0', applianceurl = '', gw='', oauth_token = NULL, oauth_secret = NULL, storage_account_claimed = 0 where fbid='$user'";// and mcid = '$mcid'";
		$result = mysql_query($q) or die("cant   $q ".mysql_error());
		logHBEvent($user,'view',"Now viewing nothing");
		if($success)
		return $mcid;
		else
		return false;
	}
	return false;
}
function wantsnoacct($user)
{
	$q = "replace into  fbtab set mcid='0', sponsorfbid='0',  targetmcid='0',  fbid='$user'";// and mcid = '$mcid'";
	$result = mysql_query($q) or die("cant   $q ".mysql_error());
	logHBEvent($user,'view',"Turned off MedCommons Account");
}


// start get standard data
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
$appname = $GLOBALS['healthbook_application_name'];
$apikey = $GLOBALS['appapikey'];
$uber = $GLOBALS['uber'];
$fb_user = $facebook->user;
connect_db();

// all these cases presume we are logged on to facebook
$ret= ($facebook->api_client->users_getInfo($fb_user,array('first_name','last_name','pic_small','sex'))); //sex
if (!$ret) {
	logHBEvent($user,'nouser',"Couldnt call users_getInfo on $user");
	die ("Couldnt get info for $facebook->user also $user");
}

$fn = mysql_real_escape_string($ret[0]['first_name']);
					$ln = mysql_real_escape_string($ret[0]['last_name']);
					$ps = mysql_real_escape_string($ret [0] ['pic_small']);
					$sx = mysql_real_escape_string($ret [0]['sex']);
					$q = "update fbtab set firstname = '$fn', lastname = '$ln', photoUrl = '$ps', sex='$sx' where fbid='$user' ";
					$result2 = mysql_query($q) or die("cant update from  $q ".mysql_error());
$page = $GLOBALS['facebook_application_url'];

$u = HealthBookUser::load($user);
if ($u==false )
{
	echo "<fb:fbml version='1.1'>redirecting to $page<fb:redirect url='$page'/></fb:fbml>"; exit;
}

if (isset($_REQUEST['newacct']))
{
	// Send them off to amazon devpay
	$returnUrl = $GLOBALS['facebook_application_url']."settings.php?paid_newacct=true";
	logHBEvent($user,'amz',"Off to amazon $returnUrl");  // was misspelled and spewing into log
	$page = $GLOBALS['devpay_redir_url'].'?src='.urlencode($returnUrl);
	echo "<fb:fbml version='1.1'>redirecting to $page<fb:redirect url='$page'/></fb:fbml>";
	exit;
}
else
if(isset($_REQUEST['paid_newacct']))
{
	// Get the amazon activation key
	$key = $_POST['ActivationKey'];
	$product = $_POST['ProductCode'];
	$mcid = insertUser($fb_user,$fn,$ln,$sex,$city,$state,$country,$pic,$key,$product);

	if($mcid === false) {
		$dash = dashboard($fb_user,false);
		$out="<fb:fbml version='1.1'>
          $dash
            <fb:error message='Error Occurred'>
                A system error occurred while creating your long term storage account and
                connecting it to your Facebook account.
             </fb:error>
          </fb:fbml>";
		echo $out;
		logHBEvent($user,'amz',"back from amazon could not make user account");
		return;
	}

	$hurl = "$uber/$mcid";
	opsMailBody(  "$appname says facebook account $user $fn $ln created medcommons account $mcid",
	"<br>A MedCommons Account was created for facebook user $fn $ln</br>".
	"<br>You can access the user's facebook profile at http://www.facebook.com/profile.php?id=$user</br>".
	"<br>You can attempt to access the user's healthurl at $hurl</br>");

	logHBEvent($user,'amz',"back from amazon healthurl is $hurl");
	$redir = $GLOBALS['facebook_application_url']."settings.php?newacct_done=true";
	echo "<fb:fbml version='1.1'><fb:redirect url='$redir' /></fb:fbml>";
	exit;
}
else
if(isset($_REQUEST['newacct_done'])) {
	$dash = dashboard($fb_user,false);
	echo "
  <fb:fbml version='1.1'>
  $dash
    <fb:success>
        <fb:message>A Long Term Account was created for $fn $ln</fb:message>
        You can keep your personal records in HealthBook. You can also become a Care Giver.
     </fb:success>
  ";
	include "confirm_account_warning.php";
	echo "</fb:fbml>";
	exit;
}
else
if (isset($_REQUEST['discon']))
{
	$mcid = dissociateUser($user);
	if($mcid === false) {
		echo "<fb:fbml version='1.1'><fb:error>
      <fb:message>A Problem Occurred While Disconnecting Your Account</fb:message> 
      <p>A system error occurred while we were disconnecting your account.</p>
      <p>Your account has been disconnected, however you may find there are still
         consents relating to your Facebook account in your old MedCommons storage account.</p>
      </fb:error></fbml>";


		logHBEvent($user,'disc',"disconnect failure from healthurl");

		exit;
	}

	$hurl = "$uber/$mcid";

	logHBEvent($user,'disc',"disconnected from healthurl $hurl");
	opsMailBody(  "$appname says facebook account $user $fn $ln disconnected from  medcommons storage and services",
	"<br>Facebook user $fn $ln disconnected from medcommons account $mcid</br>".
	"<br>You can access the user's facebook profile at http://www.facebook.com/profile.php?id=$user</br>".
	"<br>You can attempt to access the user's healthurl in the disconnected account at at $hurl</br>");

	//republish_user_profile($user);

	$page = $GLOBALS['facebook_application_url'];
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
	echo  $markup;
	exit;
}
else
{ // wants settings, first udpate from facebook
	if ($u->fbid != $u->familyfbid) $settings = noadmin_settings($u->familyfbid); 
	else
	if ($u->mcid !='0')
	$settings=disconnection_settings( $user,$u);
	else	$settings = connection_settings();
	$dash = dashboard($user,false,true);
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
$settings
</fb:fbml>
XXX;
}//end of settings


echo $markup;


?>
