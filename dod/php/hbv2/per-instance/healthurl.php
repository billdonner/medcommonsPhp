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
    $dash = hurl_dashboard($u->fbid,'Activity Log'); // was mcid behaved wierdly
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
$hurlimg = "<img src='".$GLOBALS['medcommons_images']."/hurl.png"."' alt='hurlimage' />";
dbg("appliance = $ad");
$hurl2 =$t->authorize($ad.$tmcid,$u);
dbg("hurl = $hurl2");
$eccr = $t->authorize($ad.$tmcid."/eccr",$u);
$clip = $t->authorize($ad.$tmcid."/clip",$u);
$dash = hurl_dashboard($user,'HealthURL',$u);
$editcurrent = $t->authorize($ad.$tmcid."/edit",$u);
$editcurrent2 = $t->authorize($ad."router/getPHREditSession?useSchema=11&storageId=".$tmcid,$u);

$faxUrl = $t->appliance."acct/cover.php"; // ?createCover=true&accid=".$t->mcid."&no_cover_letter=true";
$shareUrl = $GLOBALS['base_url']."share_ccr.php";

$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Health URL</fb:title>
  <script>
    var fax_box = document.getElementById('fax_box');
    function show_fax_box() {
      fax_box.setStyle('display','block');
      return false;
    } 
    var track_box = document.getElementById('track_box');
    var track_result = document.getElementById('track_result');
    function show_track_box() {
      track_box.setStyle('display','block');
      return false;
    } 
    function share_ccr() {
      track_box.setStyle('display','none');
      var ajax = new Ajax();
      ajax.responseType = Ajax.FBML;
      ajax.ondone = function(data) {
          track_result.setStyle('display','block');
          track_result.setInnerFBML(data);
      }
      ajax.post('$shareUrl',{email:document.getElementById('email').getValue(), pin: document.getElementById('pin').getValue()});
    }
    function share_hide() {
      track_result.setStyle('display','none');
      document.getElementById('email').setValue('');
      track_box.setStyle('display','block');
    } 
  </script>
  $dash
  <div class='explanation_note' style='color: #333;'>
    <h3>$hurlimg <a target='_new' href='$hurl2' title='healthURL: $hurl2'><fb:name uid='$tfbid' possessive='true' linked=false useyou='false'/> HealthURL</a></h3>
    <p>
       Edit the CCR contents by installing the HealthBook application below - <a target='_new' href='$editcurrent2' title='edit: $editcurrent2'> Edit with HealthBook</a>
      <br/>
      Fax documents into to this HealthURL by printing a barcoded cover sheet - <a href='#' onclick='show_fax_box();'>Print Preview</a>
      <div id='fax_box' style='background: white; height: 140px; width: 400px; padding: 10px; margin: 2px 2px 15px 2px; display: none;'>
        <form action='$faxUrl'>
          <input type='hidden' name='createCover' value='true'/>
          <input type='hidden' name='accid' value='$tmcid'/>
          <input type='hidden' name='no_cover_letter' value='true'/>
          <p>Enter a title for your fax:</p>
          <input type='text' name='title' value='' style='font-size: 11px;'/>
          <p>Enter a note to place on your Fax (optional) and click Print Preview:</p>
          <textarea name='note' cols="60" rows="5"></textarea>
          <br/>
          <input type='submit' class='inputbutton' name='preview' value='Print / Preview'/>
        </form>
      </div>
      Share access to this HealthURL using tracking number and PIN (at <a href='http://www.medcommons.net' target='new'>MedCommons.net</a>)
          - <a href='#' onclick='show_track_box();'>Share Now</a>
      <div id='track_box' style='background: white; height: 30px; padding: 10px; margin: 2px 2px 15px 2px; display: none;'>
          <form>
          <input type='hidden' name='accid' value='$tmcid'/>
          <b>Email</b> &nbsp;  <input type='text' id='email' name='email' value='' style='font-size: 11px; width: 16em;'/>
          <b>PIN (5 digits)</b>  &nbsp;<input type='text' id='pin' name='pin' value='' maxlength='5' size='5'  style='font-size: 11px;'/>
          &nbsp;
          <input type='button' class='inputbutton' name='send' value='Share!' onclick='share_ccr();'/>
        </form>
      </div>
      <div id='track_result' style='display: none; background-color: #fff9d7; border: solid 1px #e2c822; padding: 5px; margin: 5px 0px;'>
        
      </div>
    </p>
    <hr/>
    <p><a href='http://www.medcommons.net/healthbook/'>Install HealthBook (Windows XP and Vista Only)</a></p>
    <p><a href='http://www.medcommons.net/ddl.html' target='_new'>Install DDL DICOM Upload Utility</a></p>
  </div>
</div></div>
</fb:fbml>
XXX;
return $markup;
}

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
