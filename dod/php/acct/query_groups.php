<?
/**
 * Service returning group members for logged in user as JSON
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

  if($user->practice) {
    $query = "select u.email, concat(u.first_name,' ', u.last_name) as name, u.mcid as accid
              from groupinstances gi, groupmembers gm, users u
              where gi.groupinstanceid = ?
              and u.mcid = gm.memberaccid
              and gm.groupinstanceid = gi.groupinstanceid";
    $params = array($user->practice->providergroupid);
    $result->members = pdo_query($query,$params);
  }
  else
    $result->members = array();

  dbg("Found ".count($result->members)." members for group of user ".$user->accid);
  $result->status = "ok";
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
}
$json = new Services_JSON();
echo $json->encode($result);
?>

