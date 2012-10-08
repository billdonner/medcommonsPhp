<?
/**
 * Verifies users link and creates session variables so that their registration
 * automatically uses the predetermined account id instead of creating a new one.
 */
require_once "alib.inc.php";
require_once "JSON.php";
require_once "login.inc.php";
require_once "email.inc.php";
require_once "urls.inc.php";
require_once "mc.inc.php";
require_once "template.inc.php";
require_once "settings.php";

global $acCommonName, $acApplianceName, $acDomain, $Secure_Url;
global $URL, $NS, $SECRET;

nocache();

$result = new stdClass;
try {

    $email = req('email');
    if(!$email)
      throw new Exception("Expected parameter 'email' not provided");

    if(!is_email_address($email))
      throw new Exception("Bad format for parameter 'email'");

    $accid = req('accid');
    if(!$accid)
      throw new Exception("Expected parameter 'accid' not provided");

    if(!is_valid_mcid($accid,true))
      throw new Exception("Bad format for parameter 'accid'");

    $enc = req('enc');
    if(!$enc || !is_safe_string($enc))
      throw new Exception("Missing or bad format for parameter 'enc'");

    $params = "accid=$accid&email=".urlencode($email);
    $hmac = hash_hmac('SHA1', $params, $SECRET);

    if($hmac != $enc)
      throw new Exception("Bad value for parameter 'enc'");

    session_start();
    $_SESSION['reg_accid'] = $accid;
    $_SESSION['reg_email'] = $email;

    header("Location: register.php");
}
catch(Exception $e) {
  pdo_rollback();
  $result->status = "failed";
  $result->error = $e->getMessage();
  $loginRequired = false;
}
?>


