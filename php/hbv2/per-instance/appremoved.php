<?php
// runs in the background
require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';

function removeApplication($user)
{
	// keep the healthbook entry around, just take out the medcommons account
	// also blow away the careteam if any
	$q ="select mcid from fbtab where fbid='$user'";
	$result = mysql_query($q);
	if (!$result) {	logHBEvent($user,'mysql_error',$q.' '.mysql_error());  die("cant   $q ".mysql_error());}
	$r = mysql_fetch_array($result);
	$success = true;
	if ($r) {
		$mcid = $r[0];
		// also blow away the careteam but dont blow away caregiving  until we remove the app
		$q ="delete from careteams  where mcid='$mcid' or giverfbid='$user' ";
		$result = mysql_query($q);
		if (!$result) {	logHBEvent($user,'nysql_error',$q.' '.mysql_error());  die("cant   $q ".mysql_error());}
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
		// delete the entry from our tables
		$q = "delete from fbtab  where fbid='$user'";// and mcid = '$mcid'";
		$result = mysql_query($q);
		if (!$result) {	logHBEvent($user,'nysql_error',$q.' '.mysql_error());  die("cant   $q ".mysql_error());}
		logHBEvent($user,'unloaded',"fb $user mcid $mcid has unloaded $appname");
		if($success)		return $mcid;		else		return false;
	}
	return false;
}
// start get standard data

$appname = $GLOBALS['healthbook_application_name'];
$facebook = new Facebook($appapikey, $appsecret);
$user = $facebook->get_loggedin_user();
connect_db();
if ($user != NULL && $facebook->fb_params['uninstall'] == 1) {
	//The user has removed our app

	removeApplication($user);
	exit;
}
logHBEvent($user,'unloaded',"spurious unload fbid $user $appname");

?>
