<?
/**
 * Sets the dashboard mode setting on the user's account
 */

require_once "utils.inc.php";
require_once "alib.inc.php";

nocache();

try {
  validate_query_string();

  $groupAccId = req('accid');
  if(!$groupAccId)
    throw new Exception('Missing expected input: accid');

  if(($groupAccId !== "null") && (preg_match("/^[0-9]{16}$/D",$groupAccId) !== 1)) 
    throw new Exception('Bad format for input accid: $groupAccId');

  if($groupAccId === "null")
    $groupAccId = null;

  $info = get_validated_account_info();
  if(!$info)
    throw new Exception('Must be logged in to access this function');

  switch($groupAccId) {
    case null:
    case $info->accid:
      setcookie("mode","p",0, "/");
      break;
    default:
      setcookie("mode",False,0, "/");
  }

  dbg("setting group account id to $groupAccId for mcid = ".$info->accid);

  pdo_execute('update users set active_group_accid = ? where mcid = ?',array($groupAccId,$info->accid));

  echo "ok";
}
catch(Exception $e) {
  error_log("Failed to set active group account id ".$e->getMessage());
  header("HTTP/1.0 500 Internal Error");
  echo "Internal failure during request processing";
  exit;
}
?>
