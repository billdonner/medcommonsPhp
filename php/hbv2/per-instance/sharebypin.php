<?php
// this is required of all facebook apps

require_once 'healthbook.inc.php';
//require_once "topics.inc.php";



function sharebypin($facebook,$u,$t){
$my = "{$t->getFirstName()} {$t->getLastName()}";
	$tmcid = $t->mcid;
	$mcid = $u->mcid;
	$ad = $t->appliance;
	$user = $u->fbid;
	$tfbid = $t->fbid;

	if($tmcid==0)
	{
		$dash = dashboard($mcid);
		$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Share by PIN</fb:title>
$dash
  <fb:explanation>
    $my -- has no MedCommons Account</fb:message>
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
$dash = hurl_dashboard($user,'share',$u);
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
    <h3>Share $my's HealthURL via PIN</h3>

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
  $markup = sharebypin($facebook,$u,$t)  ;
}
echo $markup;
?>
