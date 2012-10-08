<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
require_once "dbparamsidentity.inc.php";
require_once "JSON.php";

function error($msg) {
  $json = new Services_JSON();
  $result = new stdClass;
  $result->status = "failed";
  $result->message = $msg;
  echo $json->encode($result);
  exit;
}

function cleanreq($x) {
  if(isset($_REQUEST[$x])) {
    return mysql_escape_string(get_magic_quotes_gpc() ? stripslashes($_REQUEST[$x]) : $_REQUEST[$x]);
  }
  else 
    return "";
}

mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password'])
  or error("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or ("can not connect to database $db");

$status = cleanreq('status');
$cc = cleanreq('cc');
$pid = cleanreq ('pid'); //wld072606 and not quite sure how confirmation code really works
// really should confirm that this user has access rights to the rls before doing the update

// Because we are paranoid, check the count
$result = mysql_query("select count(*) from practiceccrevents where practiceid = '$pid' and ConfirmationCode = '$cc'")
  or error("Failed to select from events table");

$count = mysql_fetch_array($result);
if($count[0] != 1) {
  error("Unexpected row count ".$count[0]);
}

mysql_query("update practiceccrevents set Status = '$status', ViewStatus='Visible' where practiceid = '$pid' and ConfirmationCode = '$cc'") 
  or error("Failed to update event");

$json = new Services_JSON();
$obj = new stdClass;
$obj->status = "ok";
$obj->savedStatus = "$status";
echo $json->encode($obj);
mysql_close();
?>
