<?
/**
 * Redirects browser to authorize joining of the current
 * user's Facebook account to a specified MedCommons appliance
 * account (or HealthURL).
 *
 * $Id: authorize_join.php 4976 2008-02-26 07:59:37Z ssadedin $
 */
require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';
require_once 'utils.inc.php';

global $oauth_consumer_key, $oauth_consumer_secret;
$app_url = $GLOBALS['facebook_application_url'];  // has a slash at the end

try {
  $hurl = req('hurl');

  // start get standard data
  $facebook = new Facebook($appapikey, $appsecret);
  $facebook->require_frame();
  $fbid = $facebook->get_loggedin_user(); 

  // Callback url that user will be redirected back to after authorizing us
  $callback = $app_url."join_account.php?fbid=$fbid&hurl=".urlencode($hurl);

  dbg("Signing with secret ".$oauth_consumer_secret);

  // Get an auth token and authorization url to redirect to
  list($request_token, $url) = ApplianceApi::authorize($oauth_consumer_key, $oauth_consumer_secret, $hurl, $callback);

  dbg("Got request token ".$request_token->key." and redirect url ".$url." signed with secret ".$oauth_consumer_secret);

  connect_db();
  $result = mysql_query("update fbtab set oauth_token = '{$request_token->key}', oauth_secret = '{$request_token->secret}' where fbid = $fbid");
  if(!$result)
    throw new Exception("failed to update oauth_token: ".mysql_error());

  // Redirect the user there to get authorization 
  echo "<fb:fbml version='1.1'><fb:redirect url='$url' /></fb:fbml>";
}
catch(Exception $e) {
  error_log("Error while initializing request token for healthurl $hurl:".$e->getMessage());
  die("<p>Apologies, your account could not be connected due to an internal system error.</p>
       <p>Please try again another time.</p>");
}
?>
