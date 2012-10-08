<?php

require_once 'login.inc.php';
require_once 'urls.inc.php';
require_once 'settings.php';
require_once 'utils.inc.php';
require_once 'pay.inc.php';
require_once 'alib.inc.php';

require 'OpenID.php';

$MIN_PW_LEN = 6;

$mcid = login_required('settings.php');
$t = template('settings.tpl.php');
$layout = template('base.tpl.php')->nest('content',$t);
$info = get_validated_account_info();

// Controls default tab displayed by tab view on page
$page = req('page','personalDetails');
$t->set("page",$page);

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
              $DB_SETTINGS);

if (count($_POST) > 0) {
  $pw0 = $_POST['pw0'];
  $pw1 = $_POST['pw1'];
  $pw2 = $_POST['pw2'];

  // Allows tab view to restore correct page after submit
  $t->set("page","password");

  $sha1 = User::compute_password($mcid, $pw0);

  $stmt = $db->prepare("SELECT sha1 FROM users WHERE mcid = :mcid");
  
  if ($stmt->execute(array("mcid" => $mcid))) {
    $row = $stmt->fetch();

    if ($sha1 == $row['sha1']) {
      if (strlen($pw1) >= $MIN_PW_LEN) {
        if ($pw1 == $pw2) {
          $sha1 = User::compute_password($mcid, $pw1);

          $stmt->closeCursor();
          $stmt = $db->prepare("UPDATE users ".
                               "SET sha1 = :sha1 ".
                               "WHERE mcid = :mcid");

          $stmt->execute(array("sha1" => $sha1, "mcid" => $mcid));

          $t = template($acTemplateFolder . 'pwchanged.tpl.php');
          echo $t->fetch();
          exit;
        }
        else
          $t->set('pw2_error', "Passwords must match");
      }
      else
        $t->set('pw1_error',
                "Passwords must be at least $MIN_PW_LEN characters");
    }
    else
      $t->set('error', "Incorrect password");
  }
  else
    $t->set('error', "No such user");

  $stmt->closeCursor();
}

$stmt = $db->prepare("SELECT email, first_name, last_name, photoUrl, amazon_user_token, amazon_pid, enable_vouchers, active_group_accid, acctype  FROM users WHERE mcid = :mcid");
$stmt->execute(array("mcid" => $mcid));
$row = $stmt->fetch();
if ($row['photoUrl'])
  $t->set('photoUrl', $row['photoUrl']);
else
  $t->set('photoUrl', gpath('Secure_Url').'/images/unknown-user.png');

$t->set('active_group_accid', $row['active_group_accid']);
$t->set('email', $row['email']);
$t->esc('first_name', $row['first_name']);
$t->esc('last_name', $row['last_name']);
$t->set('amazon_user_token', $row['amazon_user_token']);
$t->set('enable_vouchers', $row['enable_vouchers']);
$t->set('auth', $info->auth);

if($row['amazon_user_token'] != null) {
  $amazon_count = pdo_first_row("select count(*) as count from users where amazon_pid = ?", $row['amazon_pid']);
  $amazon_first_user_email = pdo_first_row("select email from users where amazon_pid = ? order by since asc limit 1",$row['amazon_pid']);
  if(($amazon_count !== null) && ($amazon_first_user_email !== null)) {
    $t->set('amazon_count',$amazon_count->count);
    $t->set('amazon_first_user_email',$amazon_first_user_email->email);
  }
}

$dashboard_mode = (($row['active_group_accid'] == $mcid) || ($row['active_group_accid']==null)) ? 'patient' : 'group';
$user = get_validated_account_info($mcid);
$practices = q_member_practices($mcid);
if($practices) {
  $is_group_member = true;
}
else
  $is_group_member = false;

$t->set('dashboard_mode', $dashboard_mode);

$t->set('is_group_member',$is_group_member);
$t->set('practices',$practices);
$t->set('practice',$user->practice?$user->practice->practicename:false);
$t->set('acctype', $row['acctype']);

$stmt->closeCursor();

$stmt = $db->prepare("SELECT id, name, format, website, source_id, username FROM identity_providers, external_users WHERE external_users.mcid = :mcid AND external_users.provider_id = identity_providers.id");
$stmt->execute(array("mcid" => $mcid));
$external_users = array();
while ($row = $stmt->fetch()) {
  $openid_url = $row['username'];
  list($head, $tail) = explode('%', canonical_openid($row['format']), 2);
  $headlen = strlen($head);
  $taillen = strlen($tail);
  if (substr_compare($openid_url, $head, 0, $headlen) == 0 &&
      substr_compare($openid_url, $tail, -$taillen) == 0)
    $username = substr($openid_url, $headlen, -$taillen);
  else
    $username = $openid_url;

  $external_users[] = array('openid_url' => $openid_url,
                            'username' => $username,
                            'name' => $row['name'],
                            'source_id' => $row['source_id'],
                            'id' => $row['id'],
                            'website' => $row['website']);
}

$t->set('idps',pdo_query('select * from identity_providers'));

$t->set('external_users', $external_users);
$t->set('accid',$mcid);

// Query the billing counters, if billing is enabled
global $acEnableBilling;
$counters = (object)array( "dicom" => 0, "faxin" => 0, "acc" =>0 );
$billingId = $mcid;
if(isset($acEnableBilling) && $acEnableBilling) {
  global $acBillingService;
  $countersXML = get_url($acBillingService."wsCounters.php?accid=".$mcid);
  $xml = simplexml_load_string($countersXML);
  if(isset($xml->binding) && isset($xml->binding->counters)) {
    $counters = $xml->binding->counters;
    $billingId = $xml->binding->billingid;
  }
  else {
    dbg("no counters found in returned xml: ".$countersXML);
  }
}
else
  dbg("Billing not enabled");

$t->set("counters",$counters);
$t->set("billingId",$billingId);


echo $layout->fetch();

?>
