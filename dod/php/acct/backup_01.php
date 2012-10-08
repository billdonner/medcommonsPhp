<?php

require 'login.inc.php';
require 'urls.inc.php';
require 'settings.php';

$mcid = login_required('backup_01.php');

$t = template($acTemplateFolder . 'backups/01_encryption.tpl.php');
$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
	      $DB_SETTINGS);

if (isset($_POST['cert']))
  $cert_text = $_POST['cert'];
else
  $cert_text = '';

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
  $cert_file = file_get_contents($_FILES['file']['tmp_name']); 
}
else
  $cert_file = '';

if ($cert_file && $cert_text) {
  /* error */
  $t->set('error', 'Enter a certificate OR upload a file.');
  $cert = $cert_text;
}
else if ($cert_file) {
  $cert = $cert_file;
}
else if ($cert_text) {
  $cert = trim($cert_text);
}
else {
  $stmt = $db->prepare("SELECT public_key FROM users WHERE mcid = :mcid");
  $stmt->execute(array("mcid" => $mcid));
  $row = $stmt->fetch();
  if ($row && $row['public_key'])
    $cert = $row['public_key'];
  else
    $cert = '';

  $stmt->closeCursor();
}

if (isset($_POST['next'])) {
  /* set certificate */
  if ($cert && strlen($cert) > 0) {
    $stmt = $db->prepare("UPDATE users SET public_key = :public_key WHERE mcid = :mcid");
    $stmt->execute(array("mcid" => $mcid, "public_key" => $cert));
  }
  else {
    $stmt = $db->prepare("UPDATE users SET public_key = NULL WHERE mcid = :mcid");
    $stmt->execute(array("mcid" => $mcid));
  }

  $stmt->closeCursor();

  header('Location: backup_02.php');
  exit;
}

$t->esc('cert', $cert);

echo $t->fetch();

?>
