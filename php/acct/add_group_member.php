<?
/**
 * Service for adding a member to a group
 */
require_once "alib.inc.php";
require_once "JSON.php";

nocache();

$result = new stdClass;
try {
  $user = get_validated_account_info();
  if(!$user)
    throw new Exception("Must be logged in");

  validate_query_string();

  $accid = req('accid');
  if(preg_match("/^[0-9]{16}$/",$accid)!==1) 
    throw new Exception("Bad format for accid: $accid");

  if(!$user->practice) 
    throw new Exception("You are not currently a member of a group");

  $groupId = $user->practice->providergroupid;

  // Check that account to add is not already in the group
  $existing = pdo_first_row("select * from groupmembers where groupinstanceid = ? and memberaccid = ?", array($groupId,$accid));
  if($existing) 
    throw new Exception("This person is already a member of the group.");

  // Add to the group
  pdo_execute("insert into groupmembers (groupinstanceid,memberaccid) values (?,?)",array($groupId,$accid));

  $result->status = "ok";
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
}
$json = new Services_JSON();
echo $json->encode($result);
?>

