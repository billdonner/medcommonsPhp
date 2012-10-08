<?
/**
 * Service for adding an address to an address book
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

  // Check that user is not already a contact
  $existing = pdo_first_row("select * from todir where td_contact_accid = ? and td_owner_accid = ?", array($accid,$user->accid));
  if($existing) 
    throw new Exception("This adddress already exists in your address book: id=".$existing->id);

  // Figure out an email address
  // First attempt to use the group email
  $email = pdo_first_row("select u.email, gi.name
                                from groupinstances gi, users u, groupadmins ga 
                                where ga.adminaccid = u.mcid
                                and ga.groupinstanceid = gi.groupinstanceid
                                and gi.accid = ?
                                order by u.since desc",array($accid));

  if($email) // If group, use group name as alias
    $alias = $email->name;
  else {// Not a group?  Just use individual email address and name for alias / contact
    $email = pdo_first_row("select email,first_name,last_name from users where mcid = ?",$accid);
    $alias = $email->first_name.' '.$email->last_name;
  }

  pdo_execute("insert into todir (id, td_alias, td_contact_list, td_contact_accid, td_owner_accid)
               values (NULL, ?, ?, ?, ?)", array($alias, $email->email, $accid, $user->accid));

  $result->status = "ok";
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
}
$json = new Services_JSON();
echo $json->encode($result);
?>

