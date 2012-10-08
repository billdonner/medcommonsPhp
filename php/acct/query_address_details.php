<?
/**
 * returns an HTML table describing an account id
 */
require_once "alib.inc.php";
require_once "template.inc.php";

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

  $details = pdo_first_row("select gi.*, u.email as 'adminemail'
                                from groupinstances gi, users u, groupadmins ga 
                                where ga.adminaccid = u.mcid
                                and ga.groupinstanceid = gi.groupinstanceid
                                and gi.accid = ?
                                order by u.since desc",array($accid));

  if($details) { 
    $count = pdo_first_row("select count(*) as size from groupmembers gm where gm.groupinstanceid = ?",array($details->groupinstanceid));
    echo template('group_detail_table.tpl.php')->set('group',$details)->set("count",$count->size)->fetch();
  }
  else
  if($acct = pdo_first_row("select * from users where mcid = ?",array($accid))) {
    echo template('account_detail_table.tpl.php')->set('user',$acct)->fetch();
  }
  else
    echo "Failed";
}
catch(Exception $e) {
  echo "<p>An error occurred while requesting data.  Please try again or contact support for help.</p>";
  error_log("Failed to query details for account $accid: ".$e->getMessage());
}
?>

