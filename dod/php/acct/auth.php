<?php

require 'login.inc.php';
require 'settings.php';
require 'common.php';
require 'mc.inc.php';
require 'OpenID.php';

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
	      $DB_SETTINGS);

$t = template($acTemplateFolder . 'link.tpl.php');

function do_login($mcids, $row) {
  global $t;

  if(is_array($mcids)) {
    $mcid = $mcids[0];
  }
  else
    $mcid = $mcids;

  // ssadedin: reload user even though we have data in $row
  // because User::load() does some magic to add more attributes
  $user = User::load($mcid);

  $user->email = $row['email'];
  $user->first_name = $row['first_name'];
  $user->last_name = $row['last_name'];
  $user->source_name = $_SESSION['mc_source_id'];
  $user->login($_SESSION['mc_next']);
}

$t->set('acOnlineRegistration', $acOnlineRegistration);

session_start();

if (count($_POST) != 0) {
  $mcid = trim($_POST['mcid']);
  $password = $_POST['password'];
  $openid = $_SESSION['mc_openid'];

  $t->esc('mcid', $_POST['mcid']);
  $t->esc('openid', $openid);
  
  if($openid && isset($_POST['anon'])) { // login not required
    // Create auth credentials
    $token = get_authentication_token($openid,$t);
    if($token) {
      header('Set-Cookie: mc_anon_auth='.$token.'; path=/');
      // Continue to next
      //error_log("## next is ".$_SESSION['mc_next']);
      redirect($_SESSION['mc_next']);
    }
  }
  else if (is_email_address($mcid)) {
    $email = $mcid;

    $stmt = $db->prepare("SELECT users.mcid, users.sha1, users.email, ".
			 "users.first_name, users.last_name ".
			 "FROM users ".
			 "WHERE users.email = :email");

    $result = $stmt->execute(array("email" => $email));
    if($result) {
      while ($row = $stmt->fetch()) {
        $sha1 = User::compute_password($row['mcid'], $password);

        if ($row['sha1'] == $sha1) {
          $stmt->closeCursor();
          link_openid_to_mcid($db, $row['mcid'], $openid, $_SESSION['mc_idp']);

          do_login(array($row['mcid'],$openid), $row);
        }
      }
    }
  }
  else {
    $mcid = clean_mcid($mcid);
    $sha1 = User::compute_password($mcid, $password);

    $stmt = $db->prepare("SELECT users.email, ".
			 "users.first_name, users.last_name ".
			 "FROM users WHERE ".
			 "users.mcid = :mcid AND ".
			 "users.sha1 = :sha1");

    $result = $stmt->execute(array("mcid" => $mcid, "sha1" => $sha1));

    if ($result) {
      $row = $stmt->fetch();

      if ($row) {
        $stmt->closeCursor();
        link_openid_to_mcid($db, $mcid, $openid, $_SESSION['mc_idp']);
        do_login(array($mcid,$openid), $row);
      }
    }
  }
  $t->set('error', 'No such user/bad password');
}
else {
  $response = $consumer->complete(get_request_url());

  $t->set('mcid', '');
  $t->set('openid', '');
  if(isset($_SESSION['mc_allow_anon_openid'])) {
    $t->set('allow_anon_openid',$_SESSION['mc_allow_anon_openid']);
  }

  if ($response->status == Auth_OpenID_CANCEL) {
    $msg = 'Verification cancelled';
  }
  else if ($response->status == Auth_OpenID_FAILURE) {
    $msg = "OpenID authentication failed: " . $response->message;
  }
  else if ($response->status == Auth_OpenID_SUCCESS) {
    $openid = $response->identity_url;

    $_SESSION['mc_openid'] = $openid;

    $t->set('openid', $openid);

    $stmt = $db->prepare("SELECT users.mcid, users.first_name, ".
			 "       users.last_name, users.email ".
			 "FROM users, external_users ".
			 "WHERE external_users.username = :username AND ".
			 "      users.mcid = external_users.mcid");

    if (!$stmt) {
      $e = $db->errorInfo();
      $msg = $e[2];
    }
    else if (!$stmt->execute(array("username" => canonical_openid($openid)))) {
      $e = $stmt->errorInfo();
      $msg = $e[2];
    }
    else {
      $row = $stmt->fetch();
      if ($row)
        do_login(array($row['mcid'],$openid), $row);
    }
  }
  else
    $msg = "Unknown OpenID response";

  if (isset($msg))
    $t->set('error', $msg);
}

echo $t->fetch();

?>
