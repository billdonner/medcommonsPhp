<?php 
/**
 * Healthbook Single Signon Support
 *
 * Renders a hidden frame that sets mc login cookie on behalf of healthbook users.
 * Supports several different cases:
 *
 *   - HB user has an mcid connected, or not
 *   - User is currently already logged on, or not
 *
 * @author bdonner@medcommons.net, ssadedin@medcommons.net
 */
require_once "dbparamsidentity.inc.php";
require_once 'login.inc.php';
require_once "alib.inc.php";
require_once "utils.inc.php";
require_once "session.inc.php";

///////////////////////////////////////////////////////////////
//
// Main start here
//
///////////////////////////////////////////////////////////////

$debug = false; 
$ob = '<html><head><title>Hidden Iframe for Healthbook to MedCommons Login</title></head><body><small><tiny>';
$endHtml = '</tiny></small></body></html>';
$fbid = (isset($_REQUEST['fbid']))? $_REQUEST['fbid']:'11';
$mcid = (isset($_REQUEST['mcid']))? $_REQUEST['mcid']:'0'; // if no mcid then pass all zeroes (have seen that~!)

verify_caller(); // will not return if invalid

log_trace_info();

// Create correct user object
aconnect_db();
if($mcid != '0') {
  $user = User::load($mcid);
}
else {
  $user = get_facebook_only_user();
}

// Set 3rd party IDP info on user
$user->source_name = 'FaceBook';
$user->user_id = $fbid;

// Array of accounts - start with their individual mcid / fbid
$accts = array($user->mcid);

// If passed, add target user's group
// which will allow access to their records
if(isset($_REQUEST['gid']))
  $accts[]=$_REQUEST['gid'];

// Create auth token
$token = get_authentication_token($accts,$t,$user);
if(!$token) 
  render("Unable to generate authentication token");

$user->authToken = $token;

// Log user in
$user->login();

// render and exit
render();

///////////////////////////////////////////////////////////////
//
// Supporting Functions
//
///////////////////////////////////////////////////////////////

/**
 * Return a User object representing a Facebook user
 */
function get_facebook_only_user() {
  global $fbid;
  $fn = isset($_REQUEST['fn']) ? $_REQUEST['fn'] : '';
  $ln = isset($_REQUEST['ln']) ? $_REQUEST['ln'] : '';

  $user = new User();
  $user->mcid = 'fbid://'.$fbid;
  $user->first_name = $fn;
  $user->last_name = $ln;
  return $user;
}

/**
 * Returns true if the correct headers are set to confirm that 
 * the user is from Facebook
 */
function is_from_facebook() {
  global $debug, $ob;

  $healthbook_refer0 = "http://apps.facebook.com/medcommons/";
  $healthbook_refer1 = "http://apps.facebook.com/healthbooktest/"; // also allow the beta site for now
  $healthbook_refer2 = "http://apps.facebook.com/healthbookbeta/"; // also allow the beta site for now

  if($debug || isset($_SERVER['HTTP_REFERER']))
  {
    if ($debug || (strpos($_SERVER['HTTP_REFERER'],$healthbook_refer0)!==false) || (strpos($_SERVER['HTTP_REFERER'],$healthbook_refer1)!==false)
     || (strpos($_SERVER['HTTP_REFERER'],$healthbook_refer2)!==false))
    {
      //echo "referred from: ".$_SERVER['HTTP_REFERER'];
      return true;
    }
  }
  return false;
}

/**
 * Check if caller is genuine and from Facebook.  If not, 
 * exit with error message.
 */
function verify_caller() {
  global $debug, $mcid, $fbid, $ob, $endHtml;

  // Check if this is really a URL from healthbook
  if(!$debug) {
    if(verify_external_application_url()) {
      $ob.="<p style='color: green;font-size:8px;'>URL VERIFIED</p>";
    }
    else // should exit here, for now, don't
      $ob.="<p style='color: red;font-size:8px;''>Whoops, your URL does not verify</p>";
  }

  if(!isset($_REQUEST['APPCODE']))
    render('APPCODE not provided');

  $appcode = $_REQUEST['APPCODE'];

  // If not from facebook, quit here!
  if(!is_from_facebook() && !$debug)
   render("you are not running inside Facebook; no cookie for you<br/>");

  // They must have facebook enabled as an IDP
  // TODO: remove this if statement when all relying parties
  // support initialization of idp in hbinitialize
  dbg("checking if facebook enabled for fbid $fbid, mcid $mcid, app $appcode");
  if(isset($_REQUEST['checkidp'])) {
    $result = pdo_query( "select e.* from external_users e, identity_providers idp
                          where e.provider_id = idp.id
                          and idp.source_id = ?
                          and e.username = ?
                          and e.mcid = ?", $appcode, $fbid, $mcid);
    if($result === false)
      render("Unable to verify that facebook is enabled for user $mcid (fbid=$fbid)");

    if(count($result)===0) 
      render("<p style='color: red;font-size:8px;'>Logon failed: User $mcid (Facebook id = $fbid) has disabled access from Facebook</p>");

    $ob .= "User $mcid has enabled access from Facebook<br/>";
  }
}

/**
 * Render output buffer and exit
 */
function render($msg="") {
  global $ob,$endHtml;
  $ob .= $msg;
  echo $ob.$endHtml; 
  exit;
}

/**
 * Log some useful info for debugging as output to screen.
 */
function log_trace_info() {
  global $ob,$mcid;
  $appliance = $_SERVER['HTTP_HOST'];
  //echo "referred from: ".$_SERVER['HTTP_REFERER'];
  if (isset($_SERVER['HTTP_REFERER'])){
    $ob .= "traversing from ".$_SERVER['HTTP_REFERER']." ";
    // facebook variables
    $now = microtime(true); $then = floatval($_REQUEST['fb_sig_time']);
    $delta = ($now - $then);
    $ob .="<br/>took ".round($delta,2)." seconds<br/>";
  }
  else
    $ob .= "http_referer: none <br/>";

  //	$ob .="fb_sig_time: ".$_REQUEST['fb_sig_time']."<br/>";
  //	$ob .="fb_sig_user: ".$_REQUEST['fb_sig_user']."<br/>";

  // if there is already a cookie set up, then display warning
  if(is_logged_in()) {
    $info = get_account_info();
    $ob .=  "currently logged on to medcommons appliance $appliance<br/>current mcid is {$info->accid} ({$info->idp}) {$info->email} {$info->fn} {$info->ln} <br/>";
    if($info->accid !== $mcid ) {
      $ob .="please note: the mcid associated with this healthbook account is different from the current cookie; will fix <br/>";
      $ob.= "re-creating mc cookie on medcommons appliance $appliance for $mcid <br>";
    }
  }
  else {
    $ob.= "creating new mc cookie on medcommons appliance $appliance for $mcid <br>";
  }
}


?>
