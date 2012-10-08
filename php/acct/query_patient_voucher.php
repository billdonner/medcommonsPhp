<?
/**
 * Service for updating the name of a group
 */
require_once "alib.inc.php";
require_once "mc.inc.php";
require_once "JSON.php";

nocache();

$result = new stdClass;
try {
  $storageId = req('accid');
  if(!is_valid_mcid($storageId, true))
    throw new ValidationFailure("Invalid value for parameter 'storageId'");
  
  $auth = req('auth');
  if(preg_match("/^[a-z0-9]{40}$/", $auth) !== 1)
    throw new ValidationFailure("Invalid value for parameter 'auth'");

  $perms = getPermissions($auth, $storageId);
  if(strpos($perms, "R") === FALSE)
    throw new ValidationFailure("Provided auth token does not have access to specified account.");
  
  $voucher = pdo_first_row("select * from modcoupons where mcid = ?", array($storageId));
  
  $result->status = "ok";
  if($voucher) {
      $result->voucher = $voucher;
      $result->found = true;
  }
  else
      $result->found = false;
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
  error_log("Update group name failed: ".$e->getMessage());
}
$json = new Services_JSON();
echo $json->encode($result);
?>


