<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
require_once "../alib.inc.php";
require_once "wslibdb.inc.php";
require_once "utils.inc.php";
require_once "login.inc.php";
require_once "mc.inc.php";

/**
 * Authorizes and grants consent to the requested external user for the given
 * account.  
 *
 * @param accid - account id 
 * @param password - password
 */
class authenticateWs extends jsonrestws {
	function jsonbody() {

    $mcid = clean_mcid(req('accid'));
    $pwd = req('pwd');

    if(!$pwd)
      return $this->error("Password not provided");

    if(!is_valid_mcid($mcid,true))
      return $this->error("Account id not in correct format");

    $sha1 = User::compute_password($mcid,$pwd);

    dbg("mcid = $mcid sha1 = $sha1");

    $users = pdo_query("SELECT u.mcid FROM users u WHERE u.mcid = ? AND u.sha1 = ? and u.acctype='USER'", $mcid, $sha1);

    if($users === false)
      return $this->error("unable to query users");

    $result = new stdClass;
    if(count($users)===1) {
      $result->status = "valid";
      $result->token = get_authentication_token(array($mcid), new stdClass) ;
      $result->mcid = $mcid;
    }
    else {
      $result->status = "invalid";
    }
    return $result;
  }
}

$x = new authenticateWs();
$x->handlews("response_authenticate");
?>
