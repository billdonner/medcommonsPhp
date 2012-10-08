<?php

require 'login.inc.php';
require 'settings.php';
require 'mc.inc.php';
require 'OpenID.php';

require 'common.php';

$mcid = login_required('/acct/settings.php');

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

session_start();

$msg = '';

if (count($_POST) != 0 && isset($_POST['openid_url']) && isset($_POST['idp'])) {

  $openid_url = trim($_POST['openid_url']);
  $idp = trim($_POST['idp']);

  $stmt = $db->prepare("SELECT id, format, name FROM identity_providers WHERE source_id = :source_id");

  $stmt->execute(array("source_id" => $idp));
  $row = $stmt->fetch();

  $_SESSION['mc_idp'] = $row['id'];

  $a = explode('%', $row['format'], 2);
  $head = $a[0];
  $tail = $a[1];

  if (substr_compare($openid_url, $head, 0, strlen($head)) != 0)
    $openid_url = $head . $openid_url;

  if (substr_compare($openid_url, $tail, -strlen($tail)) != 0)
    $openid_url .= $tail;

  $scheme = 'http';

  $stmt->closeCursor();

  if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
    $defaultPort = 443;
    $scheme .= 's';
  }
  else {
    $defaultPort = 80;
  }

  $process_url = combine_urls(get_request_url(), "link_user.php");
  $trust_root = $scheme . '://' . $_SERVER['SERVER_NAME'];

  if ($_SERVER['SERVER_PORT'] != $defaultPort)
    $trust_root .= ':' . $_SERVER['SERVER_PORT'];

  $trust_root .= dirname($_SERVER['PHP_SELF']);

  $auth_request = $consumer->begin($openid_url);

  if ($auth_request)
    redirect($auth_request->redirectUrl($trust_root, $process_url));

  $msg = 'OpenID Authentication Error';
}
else {
  $response = $consumer->complete(get_request_url());

  if ($response->status == Auth_OpenID_CANCEL) {
    $msg = 'Verification cancelled';
  }
  else if ($response->status == Auth_OpenID_FAILURE) {
    $msg = "OpenID authentication failed: " . $response->message;
  }
  else if ($response->status == Auth_OpenID_SUCCESS) {
    $openid = $response->identity_url;

    link_openid_to_mcid($db, $mcid, $openid, $_SESSION['mc_idp']);

    redirect('/acct/settings.php');
  }
  else
    $msg = "Unknown OpenID response";
}

$t = template($acTemplateFolder . 'settings.tpl.php');

if(isset($_POST['page']))
  $t->set('page',$_POST['page']);

$stmt = $db->prepare("SELECT id, name, website, format, ".
		     "source_id, username ".
		     "FROM identity_providers, external_users ".
		     "WHERE external_users.mcid = :mcid AND external_users.provider_id = identity_providers.id");
$args = array("mcid" => $mcid);

$stmt->execute($args);
$t->set('external_users', $stmt->fetchall());
$stmt->closeCursor();
$stmt = $db->prepare("SELECT first_name, last_name, email, photoUrl ".
		     "FROM users ".
		     "WHERE mcid = :mcid");
$stmt->execute($args);
$row = $stmt->fetch();

$t->set('first_name', $row['first_name']);
$t->set('last_name', $row['last_name']);
$t->set('email', $row['email']);
if ($row['photoUrl'])
  $t->set('photoUrl', $row['photoUrl']);
else
  $t->set('photoUrl', '/images/unknown-user.png');

$t->set('idp_error', $msg);
echo $t->fetch();

?>
