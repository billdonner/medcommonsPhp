<?
 /**
  * Redirects to a user's current CCR, if they have one, or falls back
  * to a provided URL instead.
  *
  * @param  accid - account id to search for current ccr
  * @param  alt - alternate URL to show if no current ccr
  * @param  widget - if passed as 'true' then pages rendered will be done without header, title etc.
  * @param  dest - optional, destination, eg: CurrentCCRWidget.action?displayUpdates
  * @author ssadedin
  */
require_once "utils.inc.php";
require_once "template.inc.php";
require_once "alib.inc.php";
require_once "mc.inc.php";
require_once "demodata_ids.inc.php";
require_once 'settings.php';

function redirect($url) {
  header('Location: ' . $url);
?><html>
  <head>
    <link rel='openid.server' value='/acct/openid.php' />
    <meta http-equiv='Location' value='<?= $url ?>' />
  </head>
  <body>
    <p>Redirecting to <a href='<?= $url ?>'><?= $url ?></a>.</p>
  </body>
</html>
<?php

  exit;
}
session_start();
aconnect_db();
dbg("qs=".$_SERVER['QUERY_STRING']);
$a = clean_mcid(req('accid'));
$alt = req('alt');
$nf = req('nf');
$dest = req('dest');
$info = get_account_info();
$openidAuth = isset($_COOKIE['mc_anon_auth']) ? $_COOKIE['mc_anon_auth'] : false;
$currentCcrGuid = getCurrentCCRGuid($a);
$widget = req('widget');
if($currentCcrGuid) { // Found current ccr?
  // Logged in?  Send them to the gateway
  if($nf !== "true") {
    $nf = gpath('Secure_Url') . '/acct/cccrredir.php?nf=true&accid=' . $a;

    if($widget=="true")
      $nf .= "&widget=true";

    if (isset($_GET['alt'])) $nf .= '&alt=' . $_GET['alt'];

    $aa = $info ? $info->accid : false;
    $auth = req('auth',$info?$info->auth:"");
    $oauth = req('oauth_token');

    // A context parameter - a hack that allows a client to pass through a flag indicating
    // how superficial aspects of the end result should be displayed
    $ctx = "";
    $c = req('c');
    if($c) {
      $ctx = "&context=".$c;
    }
    $mode="";
    if($m = req('mode')) {
      dbg("using mode ".$m);
      $mode = "&mode=".urlencode($m);
    }

    // If present, validate oauth token
    $identity = req('identity');
    $identity_type = req('identity_type');
    $name = req('identity_name');
    if($oauth) {
      try {
        verify_oauth_url();
        $oauth_params = "&oauth_token=$oauth&identity=".urlencode($identity)."&identity_type=".urlencode($identity_type)."&identity_name=".urlencode($name);
        $auth = $oauth;
      }
      catch(Exception $ex) {
        // OAuth verification may have failed due to something benign like the signature timestamp
        // timing out. So don't throw an error here, but instead continue with no oauth and let them 
        // see the page using their login credentials if they have them
        error_log("Verification of oauth token $oauth failed");
        $oauth = "";
      }
    }

    // If no dest provided assume they want to open the Current CCR
    $nopage="";
    if($dest == "") {
      $dest="currentccr?a=$a&aa=$aa";
    }
    else {
      $nopage="&nopage=true"; // use nopage to stop it displaying the progress bar since that may 
                              // not be appropriate for alternate destinations, eg. widgets
    }

    redirect(gpath('Commons_Url')."/gwredirguid.php?guid=$currentCcrGuid$nopage$ctx$mode&nf=".urlencode($nf)."&dest=".urlencode($dest)."&auth=$auth".($oauth?$oauth_params:""));
  }

  if($widget === "true") {
    // For widgets just render a vastly simpler screen that says they need to log in
    $t = template('widget.tpl.php');
    $t->set("content","<p>The requested content was not able to be accessed.</p>
              <p>It might be that the content was deleted or does not exist, or it might also be that owner of this information 
              has not granted you access or has rescinded their consent for you to access their content.</p>");
  }
  else {
    $t = template($acTemplateFolder . 'login_tn.tpl.php');
  }

  // If we came here with the 'not found' flag then remove it
  // Otherwise after login it will send us right back and fail again
  $next = preg_replace('/nf=true/','nf=false',$_SERVER['REQUEST_URI']);
  $t->set('next', $next);

  // Anon flag tells login page to allow us to login "anonymously" via openid
  $t->set('allow_anon_openid', "true");

  // Auto login for demo accounts
  if(!$info) {
    if(in_array($a,$demoIds))  {
      $t->set("autoLoginId",$a);
    }
  }

  if($nf === "true") {
    if($info)
      $t->set("msg","<p>The requested PHR was not able to be accessed with the account you are currently logged into.</p>");
  }

  echo $t->fetch();
}
else if ($alt) {
  redirect($alt);
}
else {
  if($widget === "true") {
    echo template('widget.tpl.php')->set("content","<p>This account does not yet have health information recorded.</p>")->fetch();
  }
  else
    echo template($acTemplateFolder . 'no_current_ccr.tpl.php')->fetch();
}
?>
