<?
/**
 * JSON Web service to return details of a ROIR
 *
 * @param roirId - the id of the Records Request
 * @param accid - the account id to look for service templates 
 *                matching the ROIR specified service
 */
require_once "utils.inc.php";
require_once "JSON.php";
require_once "db.inc.php";
require_once "mc.inc.php";
require_once "login.inc.php";

$VOUCHER_ID_SIZE=7;

$result = new stdClass;

try {
  $roirId = req('roirId');
  if($roirId)
    $roirId = trim($roirId);

  if(preg_match("/[A-Z]{".$VOUCHER_ID_SIZE."}/",$roirId) !== 1) 
    throw new Exception("Invalid ROI request id $roirId");

  $accid = req('accid');
  if(!is_valid_mcid($accid,true)) 
    throw new Exception("Invalid account id $accid");

  dbg("Looking up request id $roirId");

  $db = DB::get();

  $roir = $db->first_row("select * from modroi where reqid = ?",array($roirId));

  // Try and determine the service specified in the ROIR
  // The encoding of the service is a little exotic - for now we hack the decode
  $svcnum = $roir->svcnum; // default if not found
  $svc = $db->first_row("select * from modservices where accid = ? and svcnum = ?",array($accid,$svcnum));
  if($svc) {
    $svcnum = $svc->svcnum;
    dbg("Found service number $svcnum attached to ROIR $roirId");
  }
  else 
    dbg("No service found for accid = $accid, svcnum=".$roir->svcnum);

  $roir->svcnum = $svcnum;
  
  // If we got to here, we must be ok!
  $result->roir = $roir;
  $result->status = "ok";
}
catch(Exception $e) {
  error_log("Failed to locate roi request id ".(isset($roirId)?$roirId:"?").": ".$e->getMessage());
  $result->status = "failed";
  $result->message = $e->getMessage();
}

// Encode and return result
$json = new Services_JSON();
echo $json->encode($result);
