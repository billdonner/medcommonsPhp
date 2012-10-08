<?php

require_once 'login.inc.php';
require_once 'urls.inc.php';
require_once 'settings.php';
require_once 'utils.inc.php';

$MIN_PW_LEN = 6;

$mcid = login_required('claim.php');

$t = template($acTemplateFolder . 'claim.tpl.php');

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
	      $DB_SETTINGS);

if (isset($_POST['next']))
  $next = $_POST['next'];
else if (isset($_GET['next']))
  $next = $_GET['next'];
else
  $next = '/acct/home.php';

if (count($_POST) > 0) {
  $pw1 = $_POST['pw1'];
  $pw2 = $_POST['pw2'];

  if (strlen($pw1) >= $MIN_PW_LEN) {
    if ($pw1 == $pw2) {
      $sha1 = User::compute_password($mcid, $pw1);

      $stmt = $db->prepare("UPDATE users ".
			   "SET sha1 = :sha1, acctype='USER' ".
			   "WHERE mcid = :mcid");

      $stmt->execute(array("sha1" => $sha1, "mcid" => $mcid));

      header("Location: " . $next);
      exit;
    }
    else
      $t->set('pw2_error', "Passwords must match");
  }
  else
    $t->set('pw1_error',
	    "Passwords must be at least $MIN_PW_LEN characters");
}

$t->set('next', $next);

echo $t->fetch();

?>
