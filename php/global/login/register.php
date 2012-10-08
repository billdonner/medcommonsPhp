<?php

require 'login.inc.php';
require 'settings.php';
require 'mc.inc.php';

$CENTRAL_DB = 'mysql:host=mysql.internal;dbname=mcglobals';
$CENTRAL_USER = 'mc_globals';
$CENTRAL_PASS = '';

$mcid = $_REQUEST['mcid'];
if (!is_mcid($mcid)) {
  echo 'Invalid MCID';
  exit;
}

$mcid = clean_mcid($mcid);
$name = trim($_REQUEST['name']);

$db = new PDO($CENTRAL_DB, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);
$s = $db->prepare("REPLACE INTO appliance_users (name, mcid) VALUES (:name, :mcid);");

if (!$s) {
  $e = $db->errorInfo();
  echo $e[2];
  exit;
}

if (!$s->execute(array("name" => $name, "mcid" => $mcid))) {
  $e = $db->errorInfo();
  echo $e[2];
  exit;
}

echo "OK";
?>
