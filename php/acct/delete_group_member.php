<?
/**
 * Service for deleting a member from a group
 */
require_once "alib.inc.php";
require_once "JSON.php";
require_once "login.inc.php";

nocache();

$result = new stdClass;
try {
  $user = get_validated_account_info();
  if(!$user)
    throw new Exception("Must be logged in");

  validate_query_string();

  $accids = req('accid');
  if(!$accids)
    throw new Exception("Required parameter accid not provided");

  $accids = explode(',',$accids);

  pdo_begin_tx();

  // Get user's current practice / group
  if(!$user->practice) 
    throw new Exception("You are not currently a member of a group");

  $groupId = $user->practice->providergroupid;
  $loginRequired = false;
  foreach($accids as $accid) {
    if(preg_match("/^[0-9]{16}$/",$accid)!==1) 
      throw new Exception("Bad format for accid: $accid");

    // Check that account to add is not already in the group
    $existing = pdo_first_row("select * from groupmembers where groupinstanceid = ? and memberaccid = ?", array($groupId,$accid));
    if(!$existing) 
      throw new Exception("The person specified is not a member of the group.");

    // Delete from group
    pdo_execute("delete from groupmembers where memberaccid = ? and groupinstanceid = ?",array($accid, $groupId));

    // Special case:  user is deleting themselves from their group
    if($accid == $user->accid) {
      // In this case, disable purchased services, and relogin
      pdo_execute("update users set enable_vouchers = 0 where mcid = ?",array($user->accid));
      $loginRequired = true;
    }
  }
  $result->status = "ok";

  pdo_commit();
}
catch(Exception $e) {
  pdo_rollback();
  $result->status = "failed";
  $result->error = $e->getMessage();
  $loginRequired = false;
}
if($loginRequired)
  User::load($user->accid)->login();
$json = new Services_JSON();
echo $json->encode($result);
?>


