<?php

require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';

// most of this has been vacatedto settings.php on  sept 2 2008

// start get standard data
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->get_loggedin_user(); //require_login();
$appname = $GLOBALS['healthbook_application_name'];
$apikey = $GLOBALS['appapikey'];
$uber = $GLOBALS['uber'];
$fb_user = $facebook->user;
connect_db();
//logHBEvent($user,'first check',"fb user is $user $fb_user");
if (!$user)
{
	// not logged in, just do the topics
	echo gototopics($facebook,$user);
	exit;
}
// all these cases presume we are logged on to facebook
$ret= ($facebook->api_client->users_getInfo($fb_user,array('first_name','last_name','pic_small','current_location'))); //sex
if (!$ret) {
	logHBEvent($user,'nouser',"Couldnt call users_getInfo on $user");
	die ("Couldnt get info for $facebook->user also $user");
}

$fn = $ret[0]['first_name'];
$ln = $ret[0]['last_name'];
$pic = $ret [0] ['pic_small'];
$sex = ''; //$ret [0]['sex'];
$city = @$ret[0]['current_location']['city'];
$state = @$ret[0]['current_location']['state'];
$country = @$ret[0]['current_location']['country'];
$page = $GLOBALS['facebook_application_url'];

$u = HealthBookUser::load($user);
if ($u==false )
{ // set up a vestigial record if this is the first we are seeing of this user
	mysql_query("REPLACE INTO fbtab (fbid,mcid,applianceurl,targetfbid,targetmcid,groupid)
		 VALUES ('$user','0','','','', '')")
	or die("error inserting into fbtab: ".mysql_error());

	logHBEvent($user,'load',"fb $user mcid 0 virgin ");
	$u = HealthBookUser::load($user);
	if ($u==false) die ("cant find user $user just loaded");
}
// consider the likelihood that load returns false (user not found
require_once "home.inc.php";
if (!$u || ($u->mcid=='0') )
$markup =  home ($user,0,TRUE,$facebook,false,'collaborate'); // see if we can pass anything at all
else
//**** facebook user is in the table, take him into MedCommons
$markup = home ($user,$u->mcid,TRUE,$facebook,$u->appliance,'collaborate'); // see if we can pass anything at all

echo $markup; 


?>
