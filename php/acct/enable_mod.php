<?
require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "login.inc.php";
require_once "../mod/modpay.inc.php";

/**
 * Sets the flag on a user's account that determines if MOD is enabled or not for them
 */
try {
  $enable_mod = req('enable_mod');
  $enable_mod = ($enable_mod == "true") ? 1 : 0;

  $user = get_validated_account_info();

  if(!$user)
    throw new Exception("You must be logged in to access this function.");

  enable_mod($user, $enable_mod);

  // Reset the mc cookie
  $user = User::load($user->accid);
  $next = req('next','settings.php?page=amazon');
  $user->login($next);
}
catch(Exception $e) {
  error_page($e->getMessage());
}
?>

