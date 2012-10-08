<?php

require 'login.inc.php';
require 'urls.inc.php';
require 'settings.php';

$mcid = login_required('backup_01.php');

$t = template($acTemplateFolder . 'backups/02_service.tpl.php');

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
	      $DB_SETTINGS);

$stmt = $db->prepare("SELECT * FROM backups WHERE mcid = :mcid ORDER BY id DESC LIMIT 1");
$stmt->execute(array("mcid" => $mcid));
$row = $stmt->fetch();

$t->set('s3', '');
$t->set('http', '');
$t->set('ftp', '');
$t->set('scp', '');

if ($row) {
  $service = $row['destination'];
  $service = strtolower(substr($service, 0, strchr(':', $service)));
  $t->set('service', $service);
  $t->set($service, 'checked="checked"');
}
else {
  $t->set('service', 's3');
  $t->set('s3', 'checked="checked"');
}


if (isset($_POST['service'])) {
  $url = 'backup_03.php?service=' . $_POST['service'];

  header('Location: ' . $url);
}
else
  echo $t->fetch();

?>
