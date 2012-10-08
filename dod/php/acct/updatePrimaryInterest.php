<?
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/javascript");

require_once "alib.inc.php";
require_once "JSON.php";

$newPrimaryInterest = isset($_REQUEST['interest']) ? stripslashes($_REQUEST['interest']) : null;

$json = new Services_JSON();

$result = new stdClass;
$result->status="failed";

if($newPrimaryInterest === null) {
  $result->message="parameter interest is required.";
}
else {
  $info = get_account_info();
  $interests = get_user_interests(); // connects to db
  if($interests) {
    // Remove first element
    $old = array_shift($interests);
  }
  else
    $interests = array();

  // Add new
  array_unshift($interests, $newPrimaryInterest);

  $all = implode("|",$interests);

  // Update user
  if(mysql_query("update users set interests = '".mysql_real_escape_string($all)."' where mcid = ".$info->accid) !== false) {
    $result->status="ok";
  }
  else {
    $result->message="failed to update user account information";
  }
  echo $json->encode($result);
}
?>
