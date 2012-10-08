<?php
require_once "wslibdb.inc.php";
require_once "../alib.inc.php";
require_once "mc.inc.php";

/**
 * queryAccountNode.php
 *
 * Returns a json object containing information about the gateway on which an account resides
 */
require_once("JSON.php");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");


function error($msg) {
  $json = new Services_JSON();
  $res = new stdClass;
  $res->status="failed";
  $res->message=$msg;
  echo $json->encode($res);
  exit;
}

$res = new stdClass;

/*
mysql_connect($GLOBALS['DB_Connection'],
  $GLOBALS['DB_User'],
  $GLOBALS['DB_Password']
) or error("Unable to connect to database");

$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or error("Unable to select database");
*/

$accid = $_REQUEST['accid'];
if(!is_valid_mcid($accid)) {
  error("Invalid account id: $accid");
}

$json = new Services_JSON();
$res->status="ok";

$res->gw = allocate_gateway($accid);
echo $json->encode($res);
?>
