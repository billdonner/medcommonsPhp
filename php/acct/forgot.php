<?php

  /*
   * Some notes:
   * 1.  Try not to leak any information about how many accounts an email has,
   *     or even if a particular email has any accounts at all.
   */

require 'template.inc.php';
require 'email.inc.php';
require 'session.inc.php';
require 'urls.inc.php';
require 'skey.inc.php';
require 'login.inc.php';
require_once 'mc.inc.php';

$t = template($acTemplateFolder . 'forgot.tpl.php');

if (isset($_POST['skey'])) {
  /* S/Key recovery */
  $skey = trim(strtoupper($_POST['skey']));

  $t->esc('skey', $skey);

  $mcid = clean_mcid($_POST['mcid']);

  $t->esc('mcid', pretty_mcid($mcid));

  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
		$DB_SETTINGS);

  $stmt = $db->prepare("SELECT enc_skey FROM users WHERE mcid = :mcid");
  $stmt->execute(array("mcid" => $mcid));
  $row = $stmt->fetch();
  $stmt->closeCursor();

  if ($row) {
    $curr = skey_get($skey);
    $next = skey_step($curr);

    if (base64_decode($row['enc_skey']) == $next) {
      /* successful S/Key, log in */
      $stmt = $db->prepare("UPDATE users SET enc_skey = :skey WHERE mcid = :mcid");

      $stmt->execute(array("skey" => base64_encode($curr), "mcid" => $mcid));

      $token = get_authentication_token($mcid, $t);
      $user = new User();
      $user->mcid = $mcid;
      $user->email = $row['email'];
      $user->first_name = $row['first_name'];
      $user->last_name = $row['last_name'];
      $user->authToken = $otken;

      $user->login(isset($_REQUEST['next']) ? $_REQUEST['next'] : 'settings.php');
      exit;
    }
  }

  $t->set('error', 'Unknown MCID or wrong S/Key');
}
else if (isset($_POST['mcid'])) {
  /* Email instructions on password recovery */
  $mcid = trim($_POST['mcid']);
  $redirect = 'login.php?mcid=' . $mcid;

  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
		$DB_SETTINGS);

  if (strstr($mcid, '@')) {
    $email = $mcid;

    $stmt = $db->prepare('SELECT email, mcid, sha1'.
			 ' FROM users'.
			 ' WHERE email = :email AND sha1 IS NOT NULL');
    $result = $stmt->execute(array("email" => $email));
  }
  else {
    $mcid = clean_mcid($mcid);
    $email = '';
    $stmt = $db->prepare('SELECT email, mcid, sha1'.
            ' FROM users'.
            ' WHERE mcid = :mcid AND sha1 IS NOT NULL');
    $result = $stmt->execute(array("mcid" => $mcid));
  }

  $rows = array();

  $Accounts_Url = $GLOBALS['Accounts_Url'];

  if ($result) {
    while ($row = $stmt->fetch()) {
      $email = $row['email'];

      if ($email) {
	$salt = str_replace(array('+', '/'),
			    array('-', '_'),
			    base64_encode(mcrypt_create_iv(8, $MCRYPT_RAND_SOURCE)));

	$qs = 'mcid=' . $row['mcid'] . '&salt=' . $salt;

	$url = add_encrypted_query_string($Accounts_Url . 'recover.php', $qs,
					  $SECRET);

	$url = sign_query_string($SECRET . $row['sha1'], $url);

	$r = array("mcid" => pretty_mcid($row['mcid']),
		   "url" => $url);
	array_push($rows, $r);
      }
    }
  }

  $stmt->closeCursor();

  if (count($rows) > 0) {
    $d = email_template_dir();
    $t = new Template();
    $t->set('login', $Accounts_Url . 'login.php');
    $t->set('forgot', $Accounts_Url . 'forgot.php');

    if (count($rows) == 1)
      $plural = "";
    else
      $plural = "s";

    $subject = "Your {$acApplianceName} Account{$plural}";

    $t->set('plural', $plural);
    $t->set('rows', $rows);
    $t->set('acApplianceName', $acApplianceName);

    $html = $t->fetch($d . "forgotHTML.tpl.php");
    $text = $t->fetch($d . "forgotText.tpl.php");

    send_mc_email($email, $subject, $text, $html,
		  array("logo" => get_logo_as_attachment()));
  }

  header("Location: /");
  exit;
}
else {
  if (isset($_GET['mcid']))
    $t->set('mcid', $_GET['mcid']);
  else
    $t->set('mcid', '');

  $t->set('skey', '');
}

if (isset($_REQUEST['next']))
  $t->esc('next', $_REQUEST['next']);

echo $t->fetch();

?>
