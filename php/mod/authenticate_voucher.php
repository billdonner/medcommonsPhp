<?
require_once "utils.inc.php";
require_once "JSON.php";
require_once "db.inc.php";
require_once "login.inc.php";

$VOUCHER_ID_SIZE=7;

$result = new stdClass;

try {
  $voucherId = req('voucherId');
  if(preg_match("/[A-Z]{".$VOUCHER_ID_SIZE."}/",$voucherId) !== 1) 
    throw new Exception("Invalid voucher id $voucherId");

  $password = req('pwd');
  if(!$password)
    throw new Exception("Expected parameter password not provided");

  dbg("Verifying voucher $voucherId against password $password");

  // There are two states: if the voucher password has been set then
  // we need to do regular password validation against the users table
  // otherwise we need to simply check the one time password in the 
  // voucher table.
  $db = DB::get();
  $v = $db->first_row("select u.mcid, otp, sha1, acctype, auth, c.status
                       from modcoupons c, users u 
                       where c.voucherid = ? and u.mcid = c.mcid",
                       array($voucherId));
  if(!$v)
    throw new Exception("Unknown voucher id $voucherId");

  if($v->status == "issued")
    throw new Exception("Provider has not completed work on this voucher.  Please try again after the provider has confirmed contents of the HealthURL are complete.");

  if($v->acctype == "VOUCHER") { // User already set password
    if($v->sha1 !== User::compute_password($v->mcid,$password)) {
      dbg("{$v->sha1} / ".User::compute_password($v->mcid,$password)." computred from {$v->mcid} / {$password}");
      throw new Exception("Unknown voucher id / password combination");
    }
  }
  else { // Password not set, verify against vouchers table (maybe remove this?)
    if(sha1($password) !== sha1($v->otp))
      throw new Exception("Unknown voucher id / password combination");
  }
  
  // If we got to here, we must be ok!
  $result->result = new stdClass;
  $result->result->status = "valid";
  $result->result->mcid = $v->mcid; 
  $result->result->token = $v->auth; // TODO: should acquire new auth in user's name
  $result->status = "ok";
}
catch(Exception $e) {
  error_log("Failed to locate voucher id ".(isset($voucherId)?$voucherId:"?").": ".$e->getMessage());
  $result->status = "failed";
  $result->message = $e->getMessage();
}

// Encode and return result
$json = new Services_JSON();
echo $json->encode($result);
