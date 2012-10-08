<?php


$GLOBALS['facebook_config']['debug']=false;

$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_Database'] = "facebook";
$GLOBALS['DB_User']= "medcommons";


$me = $_SERVER['PHP_SELF'];

$me = substr($me,0,strrpos($me,'/')+1);

// ssadedin: some sloppy code creates urls with // instead of /
// it would be nice to clean up the sloppy code, but for the sake
// of convenience we simply coalesce the doubled slashes together here
$me = str_replace("//", "/", $me);

$GLOBALS['app_url']='http://' . $_SERVER['HTTP_HOST'] . $me;

mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User']) or die("facebook boostrap: error  connecting to database.");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die("can not connect to database $db");
$result = mysql_query("SELECT * FROM `fbapps` WHERE `key` = '$me' ") or die("$me in $db is not registered with medcommons as a healthbook application".mysql_error());
$r = mysql_fetch_object($result);
if ($r===false)  die("$me is not registered with medcommons as a healthbook application");

// simon should review all of these values given our new structure
$GLOBALS['bigapp']=($r->newfeatures==1);  // will normally be in minimode
$GLOBALS['uber'] = $r->uber_server;
$GLOBALS['uber_lookup'] =$r->uber_server."acct/ws/mcidHost.php";
$GLOBALS['appliance_key'] = $r->appliance_key;
$GLOBALS['appliance_app_code'] = $r->appliance_app_code;
$oauth_consumer_key = $r->oauth_consumer_key;
$oauth_consumer_secret = $r->oauth_consumer_secret;

require_once 'facebook.php';

// these are the branding parameters for the application
$GLOBALS['healthbook_application_name']=$r->healthbook_application_name;
$GLOBALS['healthbook_application_version']=$r->healthbook_application_version;
$GLOBALS['healthbook_application_image']=$r->healthbook_application_image;
$GLOBALS['healthbook_application_publisher'] =$r->healthbook_application_publisher;
$GLOBALS['facebook_application_url']=$r->facebook_application_url;
// behavioral features
$GLOBALS['newfeatures']=$r->newfeatures;

$GLOBALS['medcommons_images']= $r->imagery;

$GLOBALS['new_account_appliance']=$r->new_account_appliance;  // where new accounts get made
$GLOBALS['extgroupurl'] = $r->extgroupurl; 
$GLOBALS['marqueefbml']=$r->marqueefbml;
// standard facebook variables
// where this app is installed
$appapikey = $r->appapikey ;
$appsecret = $r->appsecret;
$GLOBALS['login_iframe']=$GLOBALS['new_account_appliance']."/acct/hblogin.php"; // does login for lower iframe
$GLOBALS['autoapprovemoderators'] = true;
$GLOBALS['facebook'] =$facebook;
$GLOBALS['base_url'] = $r->appcallbackurl;
$GLOBALS['appapikey'] =$appapikey;
$GLOBALS['devpay_redir_url']='https://www.medcommons.net/devpay/devpay_redir.php';

// ssadedin: mcid of group that will act as support group
if($r->new_account_support_group_mcid)
  $GLOBALS['new_account_support_group_mcid'] = $r->new_account_support_group_mcid;
?>
