<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
require_once "../alib.inc.php";
require_once "wslibdb.inc.php";
require_once "utils.inc.php";
require_once "mc.inc.php";

/**
 * Resolves a URL the correct gateway for a specified account.
 *
 * @param accid - account id 
 * @return JSON encoded object with "result" attribute equal to 
 *         URL of gateway corresponding to requested account.
 */
class resolveGatewayWs extends jsonrestws {
	function jsonbody() {

    $mcid = clean_mcid(req('accid'));
    if(!is_valid_mcid($mcid,true))
      throw new Exception("Invalid account id");

    $gw = allocate_gateway($mcid);

    return $gw;
  }
}

$x = new resolveGatewayWs();
$x->handlews("response_resolveGateway");
?>
