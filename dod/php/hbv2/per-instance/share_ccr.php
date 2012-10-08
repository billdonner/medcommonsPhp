<?
require_once 'healthbook.inc.php';
error_log("foo");

try {
  $facebook = new Facebook($appapikey, $appsecret);
  $user = $facebook->require_login();
  $u = HealthBookUser::load($user);
  $t = $u->getTargetUser();

  $email = req('email');
  $pin = req('pin');

  dbg("Sharing account of {$t->mcid} to email {$email} with pin {$pin}");

  $api = $t->getOAuthAPI();
  $result = $api->share_phr($t->mcid, $pin, $email);

  dbg("successfully shared tracking number {$result->trackingNumber}");
}
catch(Exception $e) {
  error_log("Failed to share account {$t->mcid} using pin {$pin} to email {$email}: ". $e->getMessage());
  $error = hsc($e->getMessage());
  echo "<fb:title>Error Occurred</fb:title>
       <p>We were unable to share the requested health records. The following error information was reported:</p>
       <p style='color: red;'>$error</p>
       <p>Please try again later or contact support for help.</p>";

  // Hmm, do we handle errors better?
  // eg. Account 9280779153917857 does not have a Current CCR
  exit;
}
?>
<p><b>Successfully shared!</b>  Your tracking number for this transaction is 
  <a href='<?=$t->appliance.$result->trackingNumber?>' target="_new">#<?=hsc($result->trackingNumber)?></a></p>
<p>You will need to communicate your PIN <b><?=$pin?></b> privately to the recipient <b><?=hsc($email)?></b> so that they
can access the tracking number.</p>
<p><a href='#' onclick='share_hide();'>Share Again</a></p>
