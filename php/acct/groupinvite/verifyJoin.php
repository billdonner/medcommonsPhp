<?
/**
 * Renders input form to allow existing group member to invite another user to join
 * the group.
 */
require_once "dbparamsidentity.inc.php";
require_once 'urls.inc.php';
require_once 'email.inc.php';
require_once "../alib.inc.php";
require_once "utils.inc.php";
require_once "login.inc.php";
require_once "template.inc.php";

nocache();

$VERIFY_SECRET = "lienygbdjxbdyre64528smndjkfg991kj2j2353g54ndkskskzkjdhfgfuwenmdklxlfuenegwqaaa";

$tpl = new Template();

aconnect_db();

// Find the pracitce id for account
$accid = req("a");
$email = req("e");
$practiceIds = q_member_practice_ids($accid);
$practiceId=$practiceIds[0];
$db = pdo_connect();
$p = null; // lazily loaded, see get_practice()

function render($c) {
  global $tpl;
  // Render output
  $tpl->set("content",$c);
  echo $tpl->fetch("../basic_header.tpl.php");
  exit;
}

function get_practice() {
  global $hmac,$accid,$practiceId,$db,$p;
  if($p == null) {
    $s = $db->prepare("select * from practice 
                       where practiceid = :pid");
    $s->bindParam("pid", $practiceId);
    $s->execute();
    $p = $s->fetch(PDO::FETCH_OBJ);
    if(!$p) {
      error_log("incorrect practice id in invitation url: $practiceId");
      render(new Template("badhmac.tpl.php"));
    }
  }
  return $p;
}

function get_populated_tpl($name) {
  global $hmac,$accid,$email,$practiceId;

  $c = new Template($name);
  $c->set("p",get_practice());
  $c->set("hmac",$hmac);
  $c->set("accid",$accid);
  $c->set("email",$email);
  return $c;
}

// Verify the hmac
$hmac = hash_hmac('SHA1', $accid.":".$practiceId.":".$email, $VERIFY_SECRET);
if($hmac != req('h')) {
  render(new Template("badhmac.tpl.php"));
}

try {
  if(isset($_REQUEST['join'])) { // Came here ready to join
    error_log("joining");

    // Should be logged in under account to which we will join
    if(is_logged_in()) {
      // Join the account to the group!
      $info = get_account_info();
      get_practice();

      // Check not already member of practice
      $existingPracticeIds =  q_member_practice_ids($info->accid);
      if($existingPracticeIds && $existingPracticeIds[0]) {
        error_log("account ".$info->accid." is in practice".$existingPracticeIds[0]);
        render(new Template("existing.tpl.php"));
      }

      // Add the user to the group
      error_log("adding user");
      $s = $db->prepare("insert into groupmembers (groupinstanceid,memberaccid,comment) values (?,?,'by invitation')");
      $s->bindParam(1,$p->providergroupid);
      $s->bindParam(2,$info->accid);
      if($s->execute()) {

        $c = get_populated_tpl("joined.tpl.php");

        // User has been added, but we need to reauthenticate them to validate their new credentials
        $token = get_authentication_token($info->accid, $c);
        if($token === false) {
          $c = template("badhmac.tpl.php")->set("error",$c->get("error"));
        }
        else {
          $user = new User();
          $user->mcid = $info->accid;
          $user->email = $info->email;
          $user->authToken = $token;
          $user->first_name = $info->fn;
          $user->last_name = $info->ln;
          $user->login();
        }
        error_log("reauthenticated user ".$info->accid." with token $token");
        render($c);
      }
      else {
        $e = $s->errorInfo();
        error_log("Error: ".$e[2]);
        render(get_populated_tpl("error.tpl.php"));
      }
    }
    else { // Not logged in!
      error_log("group invite user not logged in");
      $c = get_populated_tpl("login.tpl.php");
      $c->set("msg","You must successfully log in to your account to proceed.  Please log in to your account below to continue.");
      $c->set("showAccount",true);
      render($c);
    }
  }
  else { // Came here from email link

    // What to do next depends if they are logged in to the target account, and whether the account exists
    if(!q_account_exists($email)) { // it's a non-existant account.  send them to register
      $c = get_populated_tpl("register.tpl.php");
    }
    else
    if(is_logged_in()) {
      $info = get_account_info();

      if($info->email === $email) { // Logged into the right account - proceed
        $c = get_populated_tpl("checkmember.tpl.php");
      }
      else {  // Logged in, but to wrong account!
        $c = get_populated_tpl("login.tpl.php");
        $c->set("msg", "You are currently logged in to a different account to the one that this invitation is for.");
      }
    }
    else { // not logged in, ask them to log in as the target account
      $c = get_populated_tpl("login.tpl.php");
    }

    render($c);
  }
}
catch (PDOException $e) {
  error_log("Database failure: $e");
  render(new Template("badhmac.tpl.php"));
}
?>
