<?php

require 'healthbook.inc.php';
function settargets($user,$targetmcid)
{
	$app = $GLOBALS['healthbook_application_name'];

	$alink = $GLOBALS['facebook_application_url'];
	//	$q = "delete from fbtab where fbid='$user'";// and mcid = '$mcid'";
	$q = "update fbtab set targetmcid='$targetmcid' where fbid='$user'";// and mcid = '$mcid'";
	$result = mysql_query($q) or die("cant   $q ".mysql_error());
	$rows= mysql_affected_rows();


	return;
}

//start here, get arguments
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
connect_db();
$page = $GLOBALS['facebook_application_url'];
//list($mcid,$appliance) = fmcid($user);
//if ($mcid===false) die ("Internal error, fb user $user has no mcid");
if (!isset($_REQUEST['xmcid']))
{
	//user wants to turn off viewing if no fbid  specified
	settargets($user,0);

} else {
	// get incoming fbid, it is the target
	// get the mcid from the target
	$mcid = $_REQUEST['xmcid'];
	
	if ($mcid==-1)
	{
		
echo '<fb:redirect url="http://apps.facebook.com/medcommons/makesub.php" />';exit;

	}
else
	if ($mcid=='0')

	$q = "SELECT * from fbtab where fbid ='$user'  ";
	else

	$q = "SELECT * from mcaccounts where mcid ='$mcid'  ";
	$result = mysql_query($q) or die("cant $q ".mysql_error());
	$r = mysql_fetch_object($result);

	settargets ($user,$r->mcid);

}

require_once "home.inc.php";




// jumping off to outer space
echo home($user,$mcid,FALSE,$facebook,$r->applianceurl,'fcollaborate' );
exit;

?>
