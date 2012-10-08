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
 */
class resolveGatewayWs extends jsonrestws {
	function jsonbody() {

    $at = req('at');
    if(!$at)
      throw new Exception("Expected parameter 'at' not provided");

    // require auth
    validate_auth(req('auth'));

    $apps = pdo_query("select * from external_application where ea_key = ?", $at);

    if(count($apps)===0)
      throw new Exception("Token $at does not correspond to a valid application");

    return $apps[0];
  }
}

$x = new resolveGatewayWs();
$x->handlews("response_resolveGateway");
?>
