<?php

require 'template.inc.php';
require 'settings.php';
require 'login.inc.php';
require 'verify.inc.php';

$mcid = login_required('edituser.php');

$t = template($acTemplateFolder . 'edituser.tpl.php');

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

if (isset($_POST['first_name'])) {
  $ln = trim($_POST['last_name']);
  $fn = trim($_POST['first_name']);

  $stmt = $db->prepare("UPDATE users SET first_name = :fn, last_name = :ln WHERE mcid = :mcid");

  $stmt->execute(array("mcid" => $mcid, "fn" => $fn, "ln" => $ln));
  redirect('settings.php');
}
else
  $t->set('new_email', '');


$stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE mcid = :mcid");
$stmt->execute(array("mcid" => $mcid));

$row = $stmt->fetch();
$t->esc('first_name', $row['first_name']);
$t->esc('last_name', $row['last_name']);

echo $t->fetch();
?>
