<?php

require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';
function display_form($user)
{
			$dialog = <<<XXX
	<fb:dialog id="my_dialog" cancel_button=1>  <fb:dialog-title>Add A New Friend or Family Account</fb:dialog-title>
	  	<fb:dialog-content>If you create a new account for a family member you will be responsible for all charges 
	   	</fb:dialog-content> 
	   	<fb:dialog-button type="submit" value="OK" form_id="my_form" href="makesub.php"  /> 
      </fb:dialog> 
XXX;

$dash = dashboard($user,false);
$msg = <<<XXX
<fb:fbml version='1.1'><fb:title>Add New Family Member to Care For</fb:title>
$dash


<fb:explanation message='Enter Basic Info For New Patient Account'>
<p>Please enter some basic information about the patient for whom you are creating an account</p>
$dialog
<fb:editor action="?makesub.php&submit" labelwidth="100">  
<fb:editor-text label="First Name" name="fname" value=""/>  
<fb:editor-text label="Last Name" name="lname" value=""/>  
<fb:editor-custom label="Sex"> 
 <select name="sex">  <option value="0" selected>female</option>  <option value="1">male</option></select> 
  </fb:editor-custom> 
   <fb:editor-textarea label="Picture URL" name="pic"/>  
   <fb:editor-buttonset>  <fb:editor-button value="Ok"  clicktoshowdialog="my_dialog"/> 
  <fb:editor-cancel />  </fb:editor-buttonset> 
</fb:editor>
</p>
</fb:explanation>
</fb:fbml>
XXX;
return $msg;
}
function insertSponsoredUser($user,$firstName,$lastName, $sex,$city,$state,$country,$photoUrl,$mcid,$product) {

	global $oauth_consumer_key;

	// this is domain where we are going to allocate facebook accounts
	$applianceUrl = new_account_factory_appliance();
	// set the sponsorAccountId to the mcid of the caller
	$remoteurl =$applianceUrl."/router/NewPatient.action?".
	"familyName=$lastName&givenName=".urlencode($firstName).
	"&sex=".urlencode($sex).
	"&city=".urlencode($city).
	"&state=".urlencode($state).
	"&country=".urlencode($country).
	"&auth=".urlencode($oauth_consumer_key).
	"&oauth_consumer_key=".urlencode($oauth_consumer_key).
	"&photoUrl=".urlencode($photoUrl).
	"&sponsorAccountId=".urlencode($mcid).
	"&activationProductCode=".urlencode($product);

	//error_log("creating new patient with url ".$remoteurl);

	// if a support group is defined for this app, set it as the sponor so that
	// support team has access to this new patient's account
	//if(isset($GLOBALS['new_account_support_group_mcid']))
	//$remoteurl .= "&sponsorAccountId=".urlencode($GLOBALS['new_account_support_group_mcid']);

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
		error_log("Failed to create new subordinATEstorage account - NewAccount service returned failure status with error: ".$patient_info->error);
		return false;
	}

	$mcid = $patient_info->patientMedCommonsId;
	$auth = $patient_info->auth; 
	$secret = $patient_info->secret;
	$appname = $GLOBALS['healthbook_application_name'];
	$firstName = mysql_real_escape_string($firstName);
	$lastName = mysql_real_escape_string($lastName);
//	dbg("Received auth $auth and secret $secret for new patient $mcid");
	$now = time();
	logHBEvent($user ,'addsub',"$now - added subordinate usermcid $mcid $firstName $lastName");
	mysql_query("REPLACE INTO mcaccounts (mcid,applianceurl,sponsorfbid,familyfbid, targetmcid,groupid,oauth_token,oauth_secret,firstname,lastname,sex,photoURL)
	VALUES ('$mcid','$applianceUrl','$user','$user','$mcid',NULL,'$auth','$secret','$firstName','$lastName','$sex','$photoUrl')") or die("error inserting into fbtab: ".mysql_error());
//	$q = "REPLACE INTO careteams set mcid = '$mcid', giverfbid='$user',giverrole='4' ";
//	mysql_query($q) or die ("Cant $q");
	$q = "REPLACE INTO carewalls set wallmcid = '$mcid', authorfbid='$user',msg='I created this account for $firstName $lastName',time=$now ";
	mysql_query($q) or die ("Cant $q");
	
	return $mcid;
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

// get a bunch of stuff from the holder's acct

$city = //@$ret[0]['current_location']['city'];
$state = //@$ret[0]['current_location']['state'];
$country = //@$ret[0]['current_location']['country'];

'';
$page = $GLOBALS['facebook_application_url'];

if (!isset($_REQUEST['submit'])) {echo display_form($user); exit;}
$u = HealthBookUser::load($user);
if ($u==false )
{
	echo "<fb:fbml version='1.1'>redirecting to $page<fb:redirect url='$page'/></fb:fbml>";
}

// subordinate account, no fb_user, does not come from amazon, must figure how to get superiors keys
$key = '**notyet**'; //$_POST['ActivationKey'];
$product = '**notyet**'; //$_POST['ProductCode'];
$fn =  (isset($_REQUEST['fname']))?$_REQUEST['fname']:'--please set first name--';
$ln = (isset($_REQUEST['lname']))?$_REQUEST['lname']:'--please set last name--';
$sx = (isset($_REQUEST['sex']))?$_REQUEST['sex']:'--please set sex--';
$pic = (isset($_REQUEST['pic']))?$_REQUEST['pic']:'--please set pic url--';
$mcid = insertSponsoredUser($fb_user,$fn,$ln,$sx,$city,$state,$country,$pic,$u->mcid,$product);

if($mcid === false) {
	$dash = dashboard($fb_user,false);
	$out="<fb:fbml version='1.1'>
	$dash
	<fb:error message='Error Occurred'>
	A system error occurred while creating a subordinate account for mcid $u->mcid
	</fb:error>
	</fb:fbml>";
	echo $out;
	logHBEvent($user,'sub',"could not make subordinate account for mcid $u->mcid");
	exit;
}

$hurl = "$uber/$mcid";
opsMailBody(  "$appname says facebook account $user $fn $ln created medcommons account $mcid",
	"<br>A subordinate MedCommons Account was created for facebook user $fn $ln</br>".
	"<br>You can access the user's facebook profile at http://www.facebook.com/profile.php?id=$user</br>".
	"<br>You can attempt to access the user's healthurl at $hurl</br>");

//logHBEvent($user,'sub',"back from sub acct creation healthurl is $hurl");
$redir = $GLOBALS['facebook_application_url']."home.php?o=g";
echo "<fb:fbml version='1.1'><fb:redirect url='$redir' /></fb:fbml>";
exit;


?>
