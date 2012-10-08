<?php
require_once "wslibdb.inc.php";
/**
 * queryAccountCCRs.php 
 *
 * Returns JSON representing the CCRs for a given user's account
 *
 */
require_once("../JSON.php");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

function error($msg) {
  $json = new Services_JSON();
  $res->status="failed";
  $res->message=$msg;
  echo $json->encode($res);
}

mysql_connect($GLOBALS['DB_Connection'],
  $GLOBALS['DB_User'],
  $GLOBALS['DB_Password']
) or error("Unable to connect to database");

$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or error("Unable to select database");

$accid = $_REQUEST['accid'];
$result = mysql_query("select guid, status, date, tracking from ccrlog where accid=$accid order by date desc");
if(!$result)
  error("unable to select from ccr log");

while($row = mysql_fetch_object($result)) {
  $results[]=$row;
}

$json = new Services_JSON();
$res->status="ok";
$res->ccrs = $results;
echo $json->encode($res);
?>
