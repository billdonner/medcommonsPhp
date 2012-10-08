<?
  require_once "hbuser2.inc.php";

  $u = HealthBookUser::load(674921731);

  $sessions = $u->getOAuthAPI()->get_activity($u->mcid);
?>
<html>
  <body>
<?
  include "activity.php";
?>
  </body>
</html>
