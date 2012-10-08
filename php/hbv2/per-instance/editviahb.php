<?php
// this is required of all facebook apps

require_once 'healthbook.inc.php';

function editviahb($facebook,$u,$t){

 
	$tmcid = $t->mcid;
	$mcid = $u->mcid;
	$ad = $t->appliance;
	$user = $u->fbid;
	$tfbid = $t->fbid;
	$my = "{$t->getFirstName()} {$t->getLastName()}";
	if($tmcid==0)
	{
		$dash = dashboard($mcid);
		$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Edit via HealthBook (Windows Only)</fb:title>
$dash
  <fb:explanation>
    <fb:message>$my -- has no MedCommons Account</fb:message>
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
$dash = hurl_dashboard($user,'edit',$u);
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
  <h3>Edit $my CCR with HealthBook</h3>
    <h5$hurlimg <a target='_new' href='$hurl2' title='healthURL: $hurl2'><fb:name uid='$tfbid' possessive='true' linked=false useyou='false'/> HealthURL</a></h5>
    <p>
       Edit the CCR contents by installing the HealthBook application below - <a target='_new' href='$editcurrent2' title='edit: $editcurrent2'> Edit with HealthBook</a>
     
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
	$markup = editviahb($facebook,$u,$t)   ;
	}

echo $markup;
?>
