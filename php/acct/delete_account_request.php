<?
/**
 * Sends an email to cmo@medcommons.net requesting to delete an account
 */
require_once "utils.inc.php";
require_once "email.inc.php";
require_once "alib.inc.php";
require_once "mc.inc.php";
require_once "template.inc.php";

nocache();

try {
  $user = get_validated_account_info();
  if(!$user)
    throw new Exception("You must be logged in to access this function");

  // Get some unique details to provide in email so that it can be verified
  $since = pdo_first_row("select since from users where mcid = ?",array($user->accid))->since;

  $ccrlog = pdo_first_row("select date from ccrlog where accid = ? order by date desc limit 1",array($user->accid));
  $ccrlog_update_time = $ccrlog ? $ccrlog->date : "CCR Log Never Updated";

  $hurl = gpath('Secure_Url')."/".$user->accid;
  $plain_text = "User {$user->email} ( $hurl ) has requested deletion of their account.";
  $html = "<html><p><b>User Account Deletion Request</b></p><p>".
          "User {$user->email} (Account <a href='$hurl'>".pretty_mcid($user->accid).
          "</a> ) has requested deletion of their account.".
          "</p>
          <table>
            <tr><th>User Creation Date</th><td>$since</td></tr>
            <tr><th>CCR Log Update Time</th><td>$ccrlog_update_time</td></tr>
          </table>
          <p>Please visit the <a href='".gpath('Secure_Url')."/console/'>console</a> for more information about this account.</p>
          </html>";

  send_mc_email("cmo@medcommons.net", "Account Deletion Request: ".$user->email,
                 $plain_text,
                 "$html",
                 array());
}
catch(Exception $e) {
  error_page("A system error occurred while requesting deletion of your account.  Please contact cmo@medcommons.net for assistance directly.", $e->getMessage());
}

echo template("base.tpl.php")->set("content",
  "<h2>Account Deletion Request</h2>
  A request to delete your account has been created.  You will receive an email 
  shortly confirming deletion of your account.  Until that time your account will
  still be accessible.
  <br/>
  <br/>
  <p><b>Thank you for using MedCommons!</b></p>")->fetch();
?>
