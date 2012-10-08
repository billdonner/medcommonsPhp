<?
require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "login.inc.php";

/**
 * Sets the flag on a user's account that determines if MOD is enabled or not for them
 */
try {
  $user = get_validated_account_info();

  if(!$user)
    throw new Exception("You must be logged in to access this function.");

  $enable_mod = req('enable_mod');
  $enable_mod = ($enable_mod == "true") ? 1 : 0;

  $groupAccId = null;
  if($enable_mod) {
    // If enabling and user has practices, select the first practice if they have 
    // none selected already
    $practices = q_member_practices($user->accid);
    if($practices) {
      $groupAccId = $practices[0]->accid;
    }
    $mode = 'group';


    // If enabling and the user's address book is empty, add the user to their
    // own address book
    $count = pdo_first_row("select count(*) as cnt from todir where td_owner_accid = ?",array($user->accid));
    if($count && ($count->cnt==0)) {
      dbg("adding user ".$user->accid." to own address book ");
      pdo_execute("insert into todir (id, td_alias, td_contact_list, td_contact_accid, td_owner_accid)
               values (NULL, ?, ?, ?, ?)", array($user->fn.' '.$user->ln, $user->email, $user->accid, $user->accid));
    }
  }
  else {
    $mode = ($user->practice) ? 'group' : 'patient';
  }

  pdo_execute('update users set enable_vouchers = ?, active_group_accid = ? where mcid = ?', 
               array($enable_mod, $groupAccId, $user->accid));

  dbg("Updated user {$user->accid} with vouchers set to {$enable_mod}");

  // Reset the mc cookie
  $user = User::load($user->accid);
  $next = req('next','settings.php?page=amazon');
  $user->login($next);
}
catch(Exception $e) {
  error_page($e->getMessage());
}
?>

