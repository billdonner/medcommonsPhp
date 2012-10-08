<?php

require 'template.inc.php';
require 'settings.php';

$t = template($acTemplateFolder . 'search.tpl.php');
$t->set('q', '');

if (isset($_GET['q'])) {
  $q = $_GET['q'];

  $t->esc('q', $q);

  $tn = str_replace(array("-", " ", "\t", "."), "", $q);

  if (strlen($tn) == 12) {
    $q = substr($tn, 0, 4) . '-' . substr($tn, 4, 4) . '-' . substr($tn, 8, 4);
    $name = 'tracking_number';
    $display_name = 'tracking number';
  }
  else if (strlen($tn) == 16) {
    $q = substr($tn, 0, 4) . '-' . substr($tn, 4, 4) . '-' . substr($tn, 8, 4) . '-' . substr($tn, 12, 4);
    $name = 'mcid';
    $display_name = 'MCID';
  }
  else {
    $t->set('error', 'Tracking numbers are 12 digits, MCIDs are 16 digits');
    echo $t->fetch();
    exit;
  }

  $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);

  $s = $db->prepare("SELECT id, base, leap ".
		    "FROM alloc_numbers ".
		    "WHERE name= :name");
  if (!$s->execute(array("name" => $name))) {
    $e = $s->errorInfo();
    $t->set('error', "" . $e[0] . ' ' . $e[1] . ' ' . $e[2]);
    echo $t->fetch();
    exit;
  }

  $row = $s->fetch();
  if (!$row) {
    $t->set('error', "No such $display_name found");
    echo $t->fetch();
    exit;
  }

  $id = $row['id'];
  $leap = $row['leap'];
  $base = $row['base'];

  $s->closeCursor();

  $sql = "SELECT name, url, email FROM alloc_log, appliances WHERE alloc_log.numbers_id = $id AND alloc_log.seed = ($tn - $base) div $leap AND appliances.id = alloc_log.appliance_id";

  $s = $db->prepare($sql);

  if ($s && $s->execute()) {
    $row = $s->fetch();
    if ($row) {
      $name = $row['name'];
      $url = $row['url'];
      $email = $row['email'];

      if (strlen($tn) == 12) {
	$url = 'https' . substr($url, 4) . '/secure/trackemail.php?a=' . $tn;
	echo "<a href='";
	echo $url;
	echo "'>";
	echo $url;
	echo "</a>";
//	header('Location: ' . $url);
	die();
      }

    }
    else {
      $t->set('error', "No such $display_name");
    }
  }
 }

echo $t->fetch();
?>
