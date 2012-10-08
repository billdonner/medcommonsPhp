<?php

require 'healthbook.inc.php';
function settargets($user,$targetfbid,$targetmcid)
{
	$app = $GLOBALS['healthbook_application_name'];

	$alink = $GLOBALS['facebook_application_url'];
	//	$q = "delete from fbtab where fbid='$user'";// and mcid = '$mcid'";
	$q = "update fbtab set targetmcid='$targetmcid',targetfbid='$targetfbid'  where fbid='$user'";// and mcid = '$mcid'";
	$result = mysql_query($q) or die("cant   $q ".mysql_error());
	$rows= mysql_affected_rows();
	if ($targetfbid==0)
	{
		$feed_title = "<fb:userlink uid=.$user shownetwork=false /> has closed <fb:pronoun useyou=false possessive=true uid=$user /> medical records from facebook access'";
		$feed_body = 'Check out <a href='.$alink.' >'.$app.'</a>, where you can bank your family medical records</a>.';
	} else
	if ($targetfbid==$user)
	{
		$feed_title = '<fb:userlink uid="'.$user.'" shownetwork="false"/> is using '.$app.' to view <fb:pronoun useyou=false possessive=true  uid='.$user.' /> own  medical records .';
		$feed_body = 'Check out <a href='.$alink.' >'.$app.'</a>, where ' .
		'<fb:name uid="'.$user.'" firstnameonly="true" useyou="false" possessive="false"/> banks medical records</a>.';
	}
	else 	{
		// in the case where viewing someone elses records, make two entries, one from each perspective
		$feed_title = "<fb:name uid=$targetfbid /> is receiving help from  friend and CareGiver <fb:userlink uid=$user shownetwork=false />  who is utilizing 
		$app. ";
		$feed_body = 'Check out <a href='.$alink.' >'.$app.'</a>, where you provide medical records care and support for yourself and your loved ones as a HealthBook CareGiver.';


	}
	logMiniHBEvent($user,'view',$feed_title,$feed_body);
	//republish_user_profile($user);
	return ($rows==1);
}

//start here, get arguments
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
connect_db();
$page = $GLOBALS['facebook_application_url'];
list($mcid,$appliance) = fmcid($user);
if ($mcid===false) die ("Internal error, fb user $user has no mcid");
if (!isset($_REQUEST['xfbid']))
{
	//user wants to turn off viewing if no fbid  specified
	settargets($user,0,0);

	echo "<fb:fbml version='1.1'>redirecting via facebook to $page";
	echo "<fb:redirect url='$page' /></fb:fbml>";


} else {
	// get incoming fbid, it is the target
	// get the mcid from the target
	$fbid = $_REQUEST['xfbid'];
	
	if ($fbid=='0')
	{
		//user wants to turn off viewing if no fbid  specified
	settargets($user,0,0);

	echo "<fb:fbml version='1.1'>redirecting via facebook to $page";
	echo "<fb:redirect url='$page' /></fb:fbml>";
	exit;
	}

	$q = "SELECT * from fbtab where fbid ='$fbid'  ";
	$result = mysql_query($q) or die("cant $q ".mysql_error());
	$r = mysql_fetch_object($result);

	settargets ($user,$fbid,$r->mcid);

	$auth_page = $r->applianceurl."/secure/reauth.php?accid=".$r->groupid."&return=".urlencode($page);
	echo "<fb:fbml version='1.1'>redirecting via facebook to $page";
	echo "<fb:redirect url='$auth_page' /></fb:fbml>";
}
exit;

?>
