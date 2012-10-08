<?php

require_once "modpay.inc.php";
require_once "mc_oauth_client.php";
require_once "utils.inc.php";
require_once "db.inc.php";
require_once "JSON.php";

nocache();

try {
  $json = new Services_JSON();

  // The activation key should have been received from DevPay
  $ak = req('ActivationKey');
  if(!$ak)
    throw new Exception("Expected parameter 'ActivationKey' not provided");

  if(isset($_REQUEST['copy'])) {
    $cnum = req('c');
    if(!$cnum)
      throw new Exception("expected parameter c not provided");

    $db = DB::get();
    $c = $db->query("select * from modcoupons where couponum = ?",array($cnum));
    if(count($c)==0)
      throw new Exception("unknown coupon $cnum");
    $c = $c[0];
    $dest = "AccountImport.action?sourceUrl=".urlencode($c->hurl)."&sourceAuth=".$c->auth;
    $returnUrl = $next = $appliance."acct/gwredir.php?dest=".urlencode($dest);
  }
  else
    $returnUrl = $GLOBALS['mod_base_url']."/signup_completed.php";

  $regUrl = $GLOBALS['appliance_accts']."register.php?activationKey=$ak&next=".urlencode($returnUrl);
  header("Location:  $regUrl");
}
catch(Exception $ex) {
  $error = htmlentities($ex->getMessage());
  echo "<p>An error occurred while processing your payment.  Please contact MedCommons Support for help, 
    and refer to error details printed below.</p>
    <h3>Error:</h3>
    <pre>{$error}</pre>";
}
?>
