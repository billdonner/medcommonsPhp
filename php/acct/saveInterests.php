<?
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/javascript");

require_once "alib.inc.php";
require_once "JSON.php";

$newInterests = isset($_REQUEST['interests']) ? stripslashes($_REQUEST['interests']) : null;

$json = new Services_JSON();

$result = new stdClass;
$result->status="failed";

if($newInterests === null) {
  $result->message="parameter interests is required.";
}
else {
  $info = get_account_info();
  aconnect_db();
  // Update user
  if(mysql_query("update users set interests = '".mysql_real_escape_string($newInterests)."' where mcid = ".$info->accid) !== false) {
    $result->status="ok";
  }
  else {
    $result->message="failed to update user account information";
  }
  echo $json->encode($result);
}
?>
