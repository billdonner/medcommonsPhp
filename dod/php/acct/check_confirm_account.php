<?
/**
 * Checks if the user's account is confirmed and if so directs them to
 * a fully enabled dashboard with reset menus.  Otherwise sends them
 * to a page that shows only the email verification message.
 */
require_once "settings.php";
require_once "utils.inc.php";
require_once "login.inc.php";
require_once "alib.inc.php";

nocache();

$u = get_validated_account_info();
if(!$u)
  throw new Exception("You must be logged in to access this function.");

if(($u->email != null) && ($u->email != "")) { // account email is confirmed
  $u = User::load($u->accid);
  $u->login('home.php');
}
else {
  header("Location: home.php");
}
?>

