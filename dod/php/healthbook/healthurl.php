<?php
// this is required of all facebook apps

require_once 'healthbook.inc.php';
require_once "topics.inc.php";

/* NOTE: consents no longer displayed.  Left in for reference if we restore them */
function healthurl_consents($u){
	$t = $u->getTargetUser();
	$hurl2 = $t->appliance.$t->mcid."/consents?auth=".$u->token;
	// $hurl2 = $u->authorize($t->appliance."acct/cccrredir.php?accid={$t->mcid}&widget=true&dest=".urlencode("AccountSharing.action?accid={$t->mcid}"));
	$appname = $GLOBALS['healthbook_application_name'];
	$dash = hurl_dashboard($u->fbid,'consents');
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Consents</fb:title>
$dash
  <fb:explanation>
    <fb:message>$appname Consents -- <fb:name uid='$t->fbid' linked=false useyou='false'/></fb:name></fb:message><p>presenting $hurl2:</p>
<fb:iframe src="$hurl2" smartsize="false" frameborder="false" style="border: 0; width: 100%; height: 100%;"/>
</fb:explanation>
</fb:fbml>
XXX;
	return $markup;
}
function healthurl_info($u){
	$t = $u->getTargetUser();
	$hurl2 = $t->appliance.$t->mcid."/info?auth=".$u->token;
	// $hurl2 = $u->authorize($t->appliance.$t->mcid."/info");
	// $hurl2 = $u->authorize($t->appliance."/acct/cccrredir.php?accid={$t->mcid}&widget=true&dest=".urlencode("CurrentCCRWidget.action"));
	$appname = $GLOBALS['healthbook_application_name'];
	$dash = hurl_dashboard($u->fbid,'info');
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Info</fb:title>
$dash
  <fb:explanation>
    <fb:message>$appname Information -- <fb:name uid='$t->fbid' linked=false useyou='false'/></fb:name></fb:message>
    <p>presenting $hurl2:</p>
</fb:explanation>
<fb:iframe src="$hurl2" smartsize="false" frameborder="false" style="border: 0; width: 100%; height: 100%;" scrolling="no"/>
</fb:fbml>
XXX;
	return $markup;
}
function healthurl_forms($u){
	$t = $u->getTargetUser();
	$hurl2 = $t->appliance.$t->mcid."/forms?auth=".$u->token;
	// $hurl2 = $u->authorize($t->appliance.$t->mcid."/forms");
	// $hurl2 = $u->authorize($t->appliance."/acct/cccrredir.php?accid={$t->mcid}&widget=true&dest=".urlencode("CurrentCCRWidget.action?forms&accid={$t->mcid}"));
	$appname = $GLOBALS['healthbook_application_name'];
	$dash = hurl_dashboard($u->fbid,'forms');
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Forms</fb:title>
$dash
  <fb:explanation>
    <fb:message>$appname Forms -- <fb:name uid='$t->fbid' linked=false useyou='false'/></fb:name></fb:message>
  <p>presenting $hurl2:</p>
</fb:explanation>
<fb:iframe src="$hurl2" smartsize="false" frameborder="false" style="border: 0; width: 100%; height: 100%;" scrolling="no"/>
</fb:fbml>
XXX;
	return $markup;
}
function healthurl_activity($u){

  try {
    // Get the user's activity log
    $t = $u->getTargetUser();

    $appname = $GLOBALS['healthbook_application_name'];
    $dash = hurl_dashboard($u->fbid,'activity log'); // was mcid behaved wierdly
    $my = $u->my_str();
    if (!is_object($t->getOauthAPI()))
        return "
    <fb:fbml version='1.1'><fb:title>Error Occurred</fb:title>
      <p>We were unable to load your activity log.  This may be due to having an old account. Please upgrade if possible.</p>
    </fb:fbml>";
    $sessions = $t->getOAuthAPI()->get_activity($t->mcid);
    ob_start();
    include "activity.php";
    $activity_log = ob_get_contents();
    ob_end_clean();

    $markup = <<<XXX
  <fb:fbml version='1.1'><fb:title>Activity</fb:title>
  $dash
    <fb:explanation>
      <fb:message>$appname Activities -- <fb:name uid='$t->fbid' linked=false useyou='false'/></fb:name></fb:message>
      $activity_log
  </fb:explanation>
  </fb:fbml>
XXX;
  }
  catch(Exception $e) {
    $markup ="
    <fb:fbml version='1.1'><fb:title>Error Occurred</fb:title>
      <p>We were unable to load your activity log.  The following error was returned:</p>
      <p>{$e->getMessage()}</p>
    </fb:fbml>";
  }
	return $markup;
}

function healthurl($facebook,$u){
  $t = $u->getTargetUser();
	$tmcid = $t->mcid;
	$mcid = $u->mcid;
	$ad = $t->appliance;
	$user = $u->fbid;
	$tfbid = $t->fbid;

	if($tmcid==0)
	{
		$dash = dashboard($mcid);
		$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Health URL</fb:title>
$dash
  <fb:explanation>
    <fb:message><fb:name uid='$tfbid' linked=false useyou='false'/> -- has no MedCommons Account</fb:name></fb:message>
</fb:explanation>
</fb:fbml>
XXX;
		return $markup;
  }

  $hurlimg = "<img src='".$GLOBALS['images']."/hurl.png"."' alt='hurlimage' />";
  $hurl2 =$t->authorize($ad.$tmcid);
  $eccr = $t->authorize($ad.$tmcid."/eccr");
  $clip = $t->authorize($ad.$tmcid."/clip");
  $dash = hurl_dashboard($user,'HealthURL');

  // Render the file upload into variable
  ob_start();
  include "add_healthurl_document.php";
  $add_document = ob_get_contents();
  ob_end_clean();

  $markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Health URL</fb:title>
$dash
  <fb:explanation>
    <fb:message><fb:name uid='$tfbid' possessive='true' linked=false useyou='false'/> HealthURL </fb:message>
  <p>
  <table>
  <tr><td>
     <a target='_new' href='$hurl2' title='healthURL: $hurl2'>personal health record $hurlimg</a> - only <fb:name uid='$tfbid' linked=false possessive='false' useyou='true'/> can see this<br/>&nbsp;<small>(healthURL:$hurl2)</small>
  </tr></td>
  <tr><td>
    emergency health record $hurlimg - anyone can see this if they pull the card from <fb:name uid='$tfbid' possessive='true' linked=false useyou='false'/> wallet or cellphone<br/>&nbsp;<small>(healthURL:$eccr)</small>
  </tr></td>
  <tr><td>
         admissions clipboard $hurlimg - this is what <fb:name uid='$tfbid' linked=false possessive='false' useyou='true'/> can give to new doctors<br/>&nbsp;<small>(healthURL:$eccr)</small>
  </tr></td>
  </table>
  </p>
  </fb:explanation>
  <fb:explanation>
  <fb:message><h1>Add PDF to HealthURL</h1></fb:message>
    $add_document
  </fb:explanation>
</fb:fbml>
XXX;
  return $markup;
} // end healthurl

//**start here
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
if (isset ($_REQUEST['o'])) $op = $_REQUEST['o']; else $op='';
$u = HealthBookUser::load($user);
$t = $u->getTargetUser();
if ($t===false||$t->mcid===false) {
	// redirect back to indexphp
	$page = $GLOBALS['facebook_application_url'];
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}else{
	switch ($op)
	{
		case 'f': {    $markup = healthurl_forms($u)  ;break;}
		case 'a': {  $markup = healthurl_activity($u)   ;break;}
		case 'i': {    $markup = healthurl_info($u)  ;break;}
		default : {  $markup = healthurl($facebook,$u)   ;break;}
	}
}
echo $markup;
?>
