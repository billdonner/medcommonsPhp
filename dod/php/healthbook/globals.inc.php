<?
require_once "appinclude.php";  // required of all facebook apps

// these connect to the $appname database
$GLOBALS['images']='http://healthurl.myhealthespace.com/hbtest/images';
$GLOBALS['uber'] = "http://ci.myhealthespace.com"; // needed for emails to our own ops people
$GLOBALS['uber_lookup'] = "https://ci.myhealthespace.com/acct/ws/mcidHost.php";
$GLOBALS['new_account_appliance']="https://healthurl.myhealthespace.com/";  // where new accounts get made
$GLOBALS['login_iframe']=$GLOBALS['new_account_appliance']."/acct/hblogin.php"; // does login for lower iframe
$GLOBALS['autoapprovemoderators'] = true;
$GLOBALS['facebook'] =$facebook;
$GLOBALS['base_url'] = $appcallbackurl;
$GLOBALS['appapikey'] =$appapikey;
?>
