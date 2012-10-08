<?
/**
 * Service for updating the name of a group
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

  $name = req('name');
  if(!$name)
    throw new Exception("Missing parameter: name");
   
  if(strlen($name) > 64) 
    throw new Exception("Specified name is too long");

  // Get user's current practice / group
  $practices = q_member_practices($user->accid);

  if(!$practices) 
    throw new Exception("You are not currently a member of a group");

  $groupId = $practices[0]->providergroupid;

  // Update the name
  pdo_execute("update groupinstances set name = ? where groupinstanceid = ?",array($name,$groupId));

  pdo_execute("update practice set practicename = ? where practiceid = ?",array($name,$practices[0]->practiceid));

  $result->status = "ok";
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
  error_log("Update group name failed: ".$e->getMessage());
}
$json = new Services_JSON();
echo $json->encode($result);
?>


