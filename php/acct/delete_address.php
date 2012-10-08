<?
/**
 * Service for deleting addresses from an address book
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

  $accids = explode(",",req('accid'));

  dbg("Deleting account ids ".req('accid')." from address book for user ".$user->accid);

  foreach($accids as $accid) {
    if(preg_match("/^[0-9]{16}$/D",$accid)!==1) 
      throw new Exception("Bad format for accid: $accid");
  }

  foreach($accids as $contact_accid) {
    pdo_execute("delete from todir where td_owner_accid = ? and td_contact_accid = ?", array($user->accid, $contact_accid));
  }

  $result->status = "ok";
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
}
$json = new Services_JSON();
echo $json->encode($result);
?>
