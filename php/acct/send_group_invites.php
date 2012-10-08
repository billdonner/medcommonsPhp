<?
/**
 * Service for sending invitation emails to new group members
 */
require_once "alib.inc.php";
require_once "JSON.php";
require_once "login.inc.php";
require_once "email.inc.php";
require_once "urls.inc.php";
require_once "template.inc.php";
require_once "settings.php";

global $acCommonName, $acApplianceName, $acDomain, $Secure_Url;
global $URL, $NS, $SECRET;

nocache();

$result = new stdClass;
try {
  $user = get_validated_account_info();
  pdo_begin_tx();

  if(!$user)
    throw new Exception("Must be logged in");

  validate_query_string();

  $emails = req('emails');
  if(!$emails)
    throw new Exception("Required parameter emails not provided");

  $emails = explode(',',$emails);

  foreach($emails as $email) {
      if(!is_email_address($email))
          throw new Exception("Email address '$email' is not a valid address");
  }

  // Get user's current practice / group
  if(!$user->practice) 
    throw new Exception("You are not currently a member of a group");

  $groupId = $user->practice->providergroupid;

  $mcidAllocator = new SoapClient(null, array('location' => $URL, 'uri' => $NS));

  $t = template("group_invite_email_text.tpl.php")
        ->set("user",$user)
        ->set("acCommonName", $acCommonName)
        ->set("acApplianceName",$acApplianceName)
        ->set("acDomain", $acDomain)
        ->set("applianceUrl", $Secure_Url)
        ->set("groupName",$user->practice->practicename);

  foreach($emails as $email) {

      // Create an account for the user to be invited
      $accid = $mcidAllocator->next_mcid();

      dbg("Making user for account $accid");

      pdo_execute("insert into users
                          (mcid,
                           email,
                           server_id,
                           acctype,
                           enable_vouchers,
                           active_group_accid)
                   values (?,?,?,?,?,?)",
                   array($accid,$email, 0, 'GROUP_INVITE', 1, $user->practice->accid));

      // Add the user to the group
      pdo_execute("insert into groupmembers (groupinstanceid,memberaccid,comment) values (?,?,?)",
                  array($user->practice->providergroupid, $accid, ''));

      // Create a URL for verification with signature
      $url  = rtrim($Secure_Url,'/')."/acct/group_registration.php";
      $params = "accid=$accid&email=".urlencode($email);
      $hmac = hash_hmac('SHA1', $params, $SECRET);
      $url .= "?".$params."&enc=$hmac";
      $t->set("url",$url);

      dbg("Sending email to $email");
      send_mc_email($email,
                    "You have been Invited to join a Group on ".$acCommonName,
                    $t->fetch(),
                    $t->fetch("group_invite_email_html.tpl.php"),
                    array());
  }

  pdo_commit();
  dbg("Successfully invited all users");
  $result->status = "ok";
}
catch(Exception $e) {
  pdo_rollback();
  $result->status = "failed";
  $result->error = $e->getMessage();
  $loginRequired = false;
}
$json = new Services_JSON();
echo $json->encode($result);
?>


