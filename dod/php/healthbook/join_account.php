<?
/**
 * Handles callback from OAuth authorization, joining the user's
 * account to the connected account.
 *
 * This page is a return callback from the appliance after the
 * appliance OAuth procedure is invoked (see authorize_join.php).
 *
 * $Id: join_account.php 5031 2008-03-12 04:13:02Z ssadedin $
 */
require_once 'healthbook.inc.php';
require_once 'mc_oauth_client.php';
require_once 'utils.inc.php';
require_once 'hbuser2.inc.php';

global $oauth_consumer_key, $oauth_consumer_secret;
$app_url = $GLOBALS['facebook_application_url'];
$appname = $GLOBALS['healthbook_application_name'];

try {
 
  // start get standard data
  $facebook = new Facebook($appapikey, $appsecret);
  $facebook->require_frame();

  // Get and validate parameters
  $fbid = req('fbid');
  if(!$fbid)
    throw new Exception("parameter fbid is required");

  if($fbid !== $facebook->get_loggedin_user())
    throw new Exception("incorrect facebook user $fbid != ".$facebook->get_loggedin_user());

  $hurl = req('hurl');
  if(!$hurl)
    throw new Exception("parameter hurl is required");

  $u = HealthBookUser::load($fbid);
  if(!$u)
    throw new Exception("Unknown fbid: ".$fbid);

  if(!$u->token)
    throw new Exception("Request token not initialised on fbid=".$fbid);

  // Exchange request token for true access token
  $api = ApplianceApi::confirm_authorization($oauth_consumer_key, $oauth_consumer_secret, $u->token, $u->secret, $hurl);

  list($appliance_url, $mcid) = ApplianceApi::parse_health_url($hurl);

  // Update user's table with access key
  if(!mysql_query("REPLACE INTO fbtab (fbid,mcid,applianceurl,targetfbid,targetmcid,groupid,oauth_token,oauth_secret)
    VALUES ('$fbid','$mcid','".mysql_real_escape_string($appliance_url)."','$fbid','$mcid', NULL, 
            '{$api->access_token->key}','{$api->access_token->secret}')"))
    throw new Exception("error inserting into fbtab: ".mysql_error());

  // Send email to ops
  opsMailBody(  "$appname: facebook user $fbid {$u->getFirstName()} {$u->getLastName()} joined medcommons acc $mcid",
  "<html><h4>MedCommons Account $mcid was joined  for facebook user {$u->getFirstName()} {$u->getLastName()}</h4>
    <ul>
      <li>You can access the user's facebook profile at <a href='http://www.facebook.com/profile.php?id=$fbid'>http://www.facebook.com/profile.php?id=$fbid</li>
      <li>You can attempt to access the user's healthurl at <a href='$hurl'>$hurl</a></li>
    </ul>
  </body></html>"
  );

  // Send notice to user
  $fbml = "<br/>Your $appname Account was joined to a MedCommons HealthURL: <a class=applink href='$hurl' >$hurl</a>    ";
  $facebook->api_client->notifications_send($fbid, "$fbml");

  $email = "Your $appname account on FaceBook was connected to a HealthURL.
            <br/>
            <br/>
            You can visit your HealthURL outside of FaceBook any time by using the 
            following link:
            <br/>
            <br/>
            <a href='$hurl'>$hurl</a>
            <br/>";

  $txt = strip_tags(str_replace("<br>", "\n", $email));

  // Send notification email to user
  $facebook->api_client->notifications_sendEmail($fbid,"Your FaceBook Account has been connected to a HealthURL",$txt, $email);
}
catch(Exception $e) {
  error_log("Error while exchanging request token for healthurl $hurl:".$e->getMessage());
  die("<p>Apologies, your account could not be connected due to an internal system error.</p>
       <p>Please try again another time.</p>");
}
?>
<fb:fbml version='1.1'>
<?=dashboard($fbid)?>
  <fb:success>
    <fb:message>Your <?=$appname?> Account is now active</fb:message>
     We joined your <?=$appname?> with MedCommons account <?=$mcid?>. Your MedCommons profile was retained.
  </fb:success>
</fb:fbml>
