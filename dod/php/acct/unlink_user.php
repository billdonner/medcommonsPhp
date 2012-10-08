<?php

require 'login.inc.php';
require 'settings.php';

$mcid = login_required('settings.php?page=identities');

if (isset($_POST['idp']) && isset($_POST['username'])) {
  $id = $_POST['idp'];
  $username = $_POST['username'];

  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS);
  $stmt = $db->prepare("DELETE FROM external_users WHERE provider_id = :id AND mcid = :mcid AND username = :username");
  $stmt->execute(array("id" => $id, "mcid" => $mcid, "username" => $username));
}

redirect('settings.php?page=identities');

?>
