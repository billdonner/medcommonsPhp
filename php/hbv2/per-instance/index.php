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

$u = HealthBookUser::load($user);
if ($u==false )
{ // set up a vestigial record if this is the first we are seeing of this user
// all these cases presume we are logged on to facebook
$ret= ($facebook->api_client->users_getInfo($user,array('first_name','last_name','pic_small','sex'))); //sex
if (!$ret) {
	logHBEvent($user,'nouser',"Couldnt call users_getInfo on $user");
	die ("Couldnt get info for  $user");
}

$fn = mysql_real_escape_string($ret[0]['first_name']);
$ln = mysql_real_escape_string($ret[0]['last_name']);
$ps = mysql_real_escape_string($ret [0] ['pic_small']);
$sx = mysql_real_escape_string($ret [0]['sex']);
$now=time();
mysql_query("REPLACE INTO fbtab (fbid,mcid,applianceurl,sponsorfbid,familyfbid,targetmcid,groupid,
oauth_token,oauth_secret,firstname,lastname,photoUrl,sex,storage_account_claimed)
VALUES ('$user','1013062431111407','https://tenth.medcommons.net/','1107682260','1107682260', '1013062431111407','',
'79ad5793d1142c867bfd28e83e39ccb46df927c3','06c68a01db56d77878548f2b6260aae3834bdbba','$fn','$ln','$ps','$sx','1') ")
or die("error inserting into fbtab: ".mysql_error());
	$q = "REPLACE INTO careteams set mcid = '1013062431111407', giverfbid='$user',giverrole='4' ";
	mysql_query($q) or die ("Cant $q");
//	$q = "REPLACE INTO carewalls set wallmcid = '1013062431111407', authorfbid='$user',msg='Jane is a MedCommons Demo Patient. 
//	 Delete yourself from the Med Commons Family Care Team to setup your own family',time='$now' ";
//	mysql_query($q) or die ("Cant $q");

logHBEvent($user,'newuser',"$user mcid 0 $fn $ln $sx $ps ");
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
