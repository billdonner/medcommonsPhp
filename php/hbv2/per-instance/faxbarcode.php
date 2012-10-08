<?php
// this is required of all facebook apps

require_once 'healthbook.inc.php';

function faxbarcode($facebook,$u,$t){
$my = "{$t->getFirstName()} {$t->getLastName()}";
	$tmcid = $u->targetmcid;
	$user = $u->fbid;

	if($tmcid==0)
	{
		$dash = dashboard($user,false);
		$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Generate Fax Bar Code Covers for $my</fb:title>
$dash
  <fb:explanation>
    <fb:message>$my -- has no MedCommons Account</fb:name></fb:message>
</fb:explanation>
</fb:fbml>
XXX;
		return $markup;
}
$hurlimg = "<img src='".$GLOBALS['medcommons_images']."/hurl.png"."' alt='hurlimage' />";



$faxUrl = $t->appliance."acct/cover.php"; // ?createCover=true&accid=".$t->mcid."&no_cover_letter=true";


		$dash = hurl_dashboard($user,'fax');
$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Health URL</fb:title>

  $dash
  <div class='explanation_note' style='color: #333;'>
    <h3>Fax Documents Directly Into  $my's' Account</h3>

      Fax documents into to this HealthURL by printing a barcoded cover sheet 
      <div id='fax_box' style='background: white; height: 140px; width: 400px; padding: 10px; margin: 2px 2px 15px 2px; '>
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
$u = HealthBookUser::load($user);
$t = $u->getTargetUser();
if ($t===false||$t->mcid===false) {
	// redirect back to indexphp
	$page = $GLOBALS['facebook_application_url'];
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}else $markup = faxbarcode($facebook,$u,$t)   
;
echo $markup;
?>
