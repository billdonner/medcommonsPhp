<?php

require 'login.inc.php';
require 'settings.php';
require 'mc.inc.php';
require_once 'utils.inc.php';

// logindd hardwires the username and password

$_POST['mcid']='demodoctor@medcommons.net';
$_POST['password']='tester';
$_POST['next']=gpath('Accounts_Url').'/home.php';


$t = template($acTemplateFolder . 'login.tpl.php');
$t->set('acOnlineRegistration', $acOnlineRegistration);

function startswith($str, $prefix) {
  $len = strlen($prefix);

  if (strlen($str) < $len)
    return FALSE;
  else
    return substr_compare($str, $prefix, 0, $len, TRUE) == 0;
}

if (isset($_POST['next']))
  $next = $_POST['next'];
else if (isset($_GET['next']))
  $next = $_GET['next'];
else if (isset($_SERVER['HTTP_REFERER']) &&
	 strlen($_SERVER['HTTP_REFERER']) > 0 &&
	 // removed 
	 strpos("/login", $_SERVER['HTTP_REFERER']) === FALSE)
  $next = $_SERVER['HTTP_REFERER'];
else
  $next = '/acct/home.php';

$t->esc('next', $next);

/*
 * if it's a complete POST request, must contain valid email and
 * matching passwords.  If valid, then *redirect* with cookie to
 * correct user page.  If not valid, display template with error
 * inserts.
 */
if (count($_POST) == 0) {
  if (isset($_GET['mcid']))
    $mcid = $_GET['mcid'];
  else
    $mcid = '';

  $t->esc('mcid', $mcid);
}
else {
  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
		$DB_SETTINGS);

  $mcid = trim($_POST['mcid']);

  $t->esc('mcid', $mcid);

  /* OpenID? */
  if (isset($_POST['idp'])) {
    require 'common.php';

    session_start();

    $stmt = $db->prepare("SELECT id, website, name FROM identity_providers WHERE source_id = :source_id");
    $stmt->execute(array("source_id" => $_POST['idp']));
    $row = $stmt->fetch();
    $open_id = $row['website'] . $mcid;

    $_SESSION['mc_next'] = $next;
    $_SESSION['mc_idp'] = $row['id'];
    $_SESSION['mc_source_id'] = $row['name'];
    $scheme = 'http';

    if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
      $defaultPort = 443;
      $scheme .= 's';
    }
    else {
      $defaultPort = 80;
    }

    $process_url = combine_urls(get_request_url(), "auth.php");
    $trust_root = $scheme . '://' . $_SERVER['SERVER_NAME'];

    if ($_SERVER['SERVER_PORT'] != $defaultPort)
      $trust_root .= ':' . $_SERVER['SERVER_PORT'];

    $trust_root .= dirname($_SERVER['PHP_SELF']);

    $auth_request = $consumer->begin($open_id);

    if (!$auth_request) {
      $t->set('error', 'OpenID Authentication Error');
    }
    else {
      $redirect_url = $auth_request->redirectURL($trust_root,
						 $process_url);

      header("Location: " . $redirect_url);
      exit;
    }
  }
  else if (isset($_POST['password'])) {
    $password = $_POST['password'];

    if (strstr($mcid, '@')) {
      $email = $mcid;

      $stmt = $db->prepare("SELECT users.mcid, users.sha1, ".
			   "users.first_name, users.last_name ".
			   "FROM users ".
			   "WHERE users.email = :email");

      $result = $stmt->execute(array("email" => $email));

      if ($result) {
	while ($row = $stmt->fetch()) {
	  $sha1 = User::compute_password($row['mcid'], $password);

	  if ($row['sha1'] == $sha1) {
	    $token = get_authentication_token($row['mcid'], $t);

	    $user = new User();

	    $user->mcid = $row['mcid'];
	    $user->email = $email;
	    $user->authToken = $token;
	    $user->first_name = $row['first_name'];
	    $user->last_name = $row['last_name'];

	    $user->login($next);
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
	  $token = get_authentication_token($mcid, $t);

	  $user = new User();
	  $user->mcid = $mcid;
	  $user->email = $row['email'];
	  $user->first_name = $row['first_name'];
	  $user->last_name = $row['last_name'];
	  $user->authToken = $token;

	  $user->login($next);
	}
      }
    }

    $t->set('error', 'No such user/bad password');
  }
}

echo $t->fetch();

?>
