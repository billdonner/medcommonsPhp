<?
/**
 * Self-confirmation page designed to allow users to generate their
 * own skeys when an account has been created in a sponsored fashion
 * (eg. by facebook, informed sports, etc.). 
 *
 * $Id: confirm_account.php 5685 2008-07-08 23:44:06Z nvasilatos $
 */

require_once "template.inc.php";
require_once "alib.inc.php";
require_once "utils.inc.php";
require_once "mc.inc.php";
require_once "login.inc.php";
require_once "verify.inc.php";

nocache();

$accid = req("accid");

class ValidationException extends Exception {
}

try {
  if(!is_valid_mcid($accid,true))
    throw new Exception("Parameter accid is missing / incorrect format");
  
  // Verify oauth token
  verify_oauth_url();

  // Check that token has rights to account specified
  $auth = $_REQUEST['oauth_token'];

  $permissions = getPermissions($auth,$accid);
  dbg("Token $auth has rights $permissions to account $accid");
  if(strpos($permissions,"W") === FALSE)
    throw new Exception("Attempt to confirm account without correct rights: token $auth has only $permissions rights to account $accid");

  // Check that account is in SPONSORED state
  $users = pdo_query("select * from users where mcid = ?",$accid);
  if(($users === false) || (count($users) === 0))
    throw new Exception("Attempt to confirm non-existent user: $accid");

  $u = $users[0];

  // Check the account type - only SPONSORED users can
  // upgrade their state
  if($u->acctype !== "SPONSORED")
    throw new Exception("User $accid is in incorrect state ({$u->acctype}).  User must be in SPONSORED state to allow confirmation");

  $t = template("confirm_account.tpl.php");

  // Set default values
  $email = req("email","");
  $pwd1 = req("pwd1","");
  $pwd2 = req("pwd2","");
  $t->set("email",$email)->set("pwd1",$pwd1)->set("pwd2",$pwd2);

  if(isset($_POST['submit'])) {
    $invalid = array();
    if(!check_email_address(req("email")))
      $invalid[]='email';

    if($pwd1 === "")
      $invalid[]='pwd1';

    if($pwd2 === "")
      $invalid[]='pwd2';

    // Check the email is not in use yet
    $users = pdo_query("select * from users where email = ?", $email);
    if(count($users) > 0) {
      $invalid[] = "email";
      $t->set("msg", "The email you specified is already in use for another account.  Please choose a different email address.");
    }

    if(count($invalid) > 0) {
      $t->set("invalid",$invalid);
      throw new ValidationException();
    }

    if($pwd1 !== $pwd2) {
      $t->esc("msg","The passwords you entered did not match.  Please check them and try again.");
      throw new ValidationException();
    }

    // Calculate password hash
    $sha1 = User::compute_password($accid, $pwd1);

    // Everything looks good.  Proceed to issue SKeys and Email
    pdo_execute("update users set sha1 = ?, acctype = 'UNVALIDATED' where mcid = ?",array($sha1,$accid));

    $user = new User();
    $user->mcid = $accid;
    $user->email = $email;
    $user->authToken = get_authentication_token($accid,$t);
    if($user->authToken === false)
      throw new Exception("Unable to generate authentication token for account $accid");

    // Send the registration email
    verify_new_email($accid, $email);

    // Successful!
    $notify_url = req("notify");
    if($notify_url) {
      dbg("Calling notification url ".$notify_url);
      $notification_result = get_url($notify_url);
      dbg("Got notification result ".$notification_result);
    }
    echo template("base.tpl.php")->set("title","Your Account Has Been Confirmed")
                                 ->set("head","")
                                 ->set("content", template("account_confirmed_msg.tpl.php"))->fetch();
    exit;
  }
  
  echo template("base.tpl.php")
    ->set("title","Confirm Your MedCommons Storage Account")
    ->set("head","")
    ->set("content",$t)->fetch();
}
catch(ValidationException $ex) {
  echo template("base.tpl.php")
    ->set("title","Confirm Your MedCommons Storage Account")
    ->set("head","")
    ->set("content",$t)->fetch();
}
catch(Exception $ex) {
  error_log("Failed to confirm account $accid: ".$ex->getMessage());
  echo template("base.tpl.php")
    ->set("title","Error Occurred - Confirm Your MedCommons Storage Account")
    ->set("head","")
    ->set("content","<br/><br/><p>Apologies: an error occurred while confirming your account.</p><br/><p>Please try again later.</p>")->fetch();
}
?>

