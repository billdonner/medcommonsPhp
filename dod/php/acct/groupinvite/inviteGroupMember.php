<?
/**
 * Renders input form to allow existing group member to invite another user to join
 * the group.
 */
require_once "dbparamsidentity.inc.php";
require_once 'urls.inc.php';
require_once 'settings.php';
require_once 'email.inc.php';
require_once "../alib.inc.php";
require_once "utils.inc.php";
require_once "template.inc.php";

$VERIFY_SECRET = "lienygbdjxbdyre64528smndjkfg991kj2j2353g54ndkskskzkjdhfgfuwenmdklxlfuenegwqaaa";

$tpl = new template("base.tpl.php");
$tpl->set("head","");

// If they just removed themselves then show the "you removed yourself message"
if(isset($_GET['selfremoved'])) {
   $tpl->set("content",template("selfremoved.tpl.php")); 
   echo $tpl->fetch();
   exit;
}

// Get login information
list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not logged on
$db = aconnect_db(); // connect to the right database
$practiceIds = q_member_practice_ids($accid);
$practiceId=$practiceIds[0];

// Get the practice details
$result = mysql_query("select * from practice where practiceid = $practiceId");
if($result) {
  $practice = mysql_fetch_object($result); 
}
else {
  echo "Internal error: unable to query practice details";
  exit;
}


// Form submitted?
if(isset($_POST['email'])) {
  $invitee = req('email');

  // Validate email address (hacky)
  if(!preg_match("/.+@.+\\.[a-z]+/i", $invitee)) {
    $content = new Template("inviteGroupMember.tpl.php");
    $content->set("badEmail",true);
    $content->set("members", q_practice_members($practiceId));
    $content->set("accid", $accid);
    $content->set("practice", $practice);
  }
  else { // Ok - send
    // Send the email
    global $VERIFY_SECRET;

    // NOTE: hmac the practice id as well, so that the invite can only work
    // if the same practice is computed when it is activated.
    $hmac = hash_hmac('SHA1', $accid.':'.$practiceId.':'.$invitee, $VERIFY_SECRET);
    $url = detrail($GLOBALS['Accounts_Url']) . '/groupinvite/verifyJoin.php?a='.$accid.'&e='.urlencode($invitee).'&h='.$hmac;

    // Construct template
    $emailTpl = new Template();
    $emailTpl->set("url",$url);

    $d = email_template_dir();

    $text = $emailTpl->fetch($d . "inviteText.tpl.php");
    $html = $emailTpl->fetch($d . "inviteHTML.tpl.php");

    // Send email
    error_log("Sending email: $text");
    send_mc_email($invitee, "Invitation to Join $acApplianceName Group",
                  $text, $html,
                  array('logo' => get_logo_as_attachment()));

    // Redirect to confirmation message
    header("Location: inviteGroupMember.php?confirmed");
    exit;
  }
}
else
if(isset($_GET['confirmed'])) {
  $content = new Template("inviteConfirmed.tpl.php");
}
else {
  $content = new Template("inviteGroupMember.tpl.php");
  $content->set("members", q_practice_members($practiceId));
  $content->set("practice", $practice);
  $content->set("info", get_account_info());
  $content->set("accid", $accid);
  if(isset($_GET['removed'])) {
    $content->set("msg","The group member has been successfully removed.");
  }
}

// Render output
$tpl->set("content",$content);
echo $tpl->fetch();
?>
