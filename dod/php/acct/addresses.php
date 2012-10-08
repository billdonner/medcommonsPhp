<?
/**
 * Service returning address book for logged in user
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

  $query = "select d.id, d.td_contact_list as 'email', d.td_contact_accid as 'accid', coalesce(gi.name , concat(u.first_name,' ',u.last_name)) as 'name'
                                  from todir d
                                  left join groupinstances gi on gi.accid = d.td_contact_accid,
                                  users u
                                  where d.td_owner_accid = ?
                                  and u.mcid = d.td_contact_accid";
  $params = array($user->accid);
  if(req('query')) {
    $query.= " and coalesce(gi.name , concat(u.first_name,' ',u.last_name)) like concat('%',?,'%')";
    $params[]=req('query');
  }

  $result->members = pdo_query($query,$params);
  dbg("Found ".count($result->members)." addresses for account ".$user->accid);
  $result->status = "ok";
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
}
$json = new Services_JSON();
echo $json->encode($result);
?>
