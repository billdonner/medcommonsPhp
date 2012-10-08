<?php

require 'login.inc.php';
require 'urls.inc.php';

/* Must have enc= and hmac= fields on the query string */
if (!isset($_GET['enc']) || !isset($_GET['hmac'])) {
  header("Location: forgot.php");
  exit;
}

$MIN_PW_LEN = 6;

$enc = $_GET['enc'];
$hmac = $_GET['hmac'];

$t = template($acTemplateFolder . 'recover.tpl.php');
$t->set('enc', $enc);
$t->set('hmac', $hmac);

if (count($_POST) > 0) {
  $pw1 = $_POST['pw1'];
  $pw2 = $_POST['pw2'];

  $qs = decrypt_urlsafe_base64($enc, $SECRET);
  parse_str($qs, $d);

  $mcid = $d['mcid'];

  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
		$DB_SETTINGS);

  $stmt = $db->prepare("SELECT sha1 FROM users WHERE mcid = :mcid");

  if (!$stmt) {
    $e = $db->errorInfo();
    $t->esc('error', $e[2]);
  }
  else if (!$stmt->execute(array("mcid" => $mcid))) {
    $e = $stmt->errorInfo();
    $t->esc('error', $e[2]);
  }
  else {
    $row = $stmt->fetch();

    if (!$row) {
      $t->esc('error', 'No such user');
    }
    else if (!is_signed_query_string_valid($SECRET . $row['sha1'],
					   $_SERVER['QUERY_STRING'])) {
      $t->esc('error', 'Password has already been updated');
    }
    else if (strlen($pw1) < $MIN_PW_LEN) {
      $t->set('pw1_error',
 	      "Passwords must be at least $MIN_PW_LEN characters");
    }
    else if (strcmp($pw1, $pw2) != 0) {
      $t->set('pw2_error', "Passwords must match");
    }
    else {
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
  }
}

echo $t->fetch();

?>
