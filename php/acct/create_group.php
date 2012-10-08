<?
/**
 * Creates a group for the current user and returns to the settings groups page
 */
require_once "alib.inc.php";
require_once "JSON.php";
require_once "settings.php";

nocache();

$result = new stdClass;
try {
  $user = get_validated_account_info();
  if(!$user)
    throw new Exception("Must be logged in");

  // Get user's current practice / group
  $practices = q_member_practices($user->accid);
  if($practices) 
    error_page("You are already a member of a group.  Please leave your existing group before creating a new one.",
	       "Member of group ".$practices[0]->providergroupid.' Associated with practice '.$practices[0]->practicename);

  pdo_begin_tx();

  // Add group entry
  $groupName = $user->fn.' '.$user->ln.' Group';

  create_group($user, $groupName);

  pdo_commit();

  $next = req('next','settings.php?page=groups');
  header("Location: $next");
}
catch(Exception $e) {
  pdo_rollback();
  error_page("Unable to create your group",$e);
}
?>


