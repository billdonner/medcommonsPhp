<?
require_once "mc.inc.php";
require_once "utils.inc.php";
require_once "JSON.php";
require_once "voucher_host.inc.php";

nocache();

$VOUCHER_ID_SIZE=7;

$result = new stdClass;

try {
  $voucherId = req('voucherId');
  if(preg_match("/[A-Z]{".$VOUCHER_ID_SIZE."}/",$voucherId) !== 1) 
    throw new Exception("Invalid voucher id $voucherId");

  $server = locate_voucher($voucherId);

  $result->status = "ok";
  $result->server = $server;
}
catch(Exception $e) {
  error_log("Failed to locate voucher id ".(isset($voucherId)?$voucherId:"?").": ".$e.getMessage());
  $result->status = "failed";
  $result->error = $e->getMessage();
}

// Encode and return result
$json = new Services_JSON();
echo $json->encode($result);
