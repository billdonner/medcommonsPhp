<?
/**
 * Account Confirmation
 *
 * This page redirects the user over to their appliance with an OAuth signed
 * URL that allows them to enter their email address and begin the validation
 * process to generate and print their SKeys.
 *
 * $Id: confirm_account.php 5182 2008-04-15 08:48:13Z ssadedin $
 */
require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';
require_once 'utils.inc.php';

try {
  // start by getting standard data
  $facebook = new Facebook($appapikey, $appsecret);
  $facebook->require_frame();
  $fbid = $facebook->get_loggedin_user(); 

  $u = HealthBookUser::load($fbid);

  // Compute a callback URL that the result should be reported to
  $returnUrl = $GLOBALS['base_url']."account_confirmed.php?fbid=".$u->fbid;

  $url = $u->authorize($u->appliance."acct/confirm_account.php?accid=".$u->mcid."&notify=".urlencode($returnUrl));

  // Redirect the user there to get authorization 
  echo "<fb:fbml version='1.1'><fb:redirect url='$url' /></fb:fbml>";
}
catch(Exception $e) {
  error_log("Error while initializing redirecting user to account confirmation page:".$e->getMessage());
  die("<p>Apologies, an internal system error occurred while attempting to send you to your Account Confirmation page.</p>
       <p>Please try again another time.</p>");
}
?> 
