<?php

require 'settings.php';
require 'mc.inc.php';

if (isset($_REQUEST['from']))
  $from = $_REQUEST['from'];
else if (isset($_SERVER['HTTP_REFERER']))
  $from = $_SERVER['HTTP_REFERER'];
else
  $from = '/login.php';

if ($i = strrpos($from, '?')) $from = substr($from, 0, $i);

if (isset($_REQUEST['p']))
  $error = $from . '?p=' . $_REQUEST['p'] . '&error=';
else
  $error = $from . '?error=';

$q = $_REQUEST['q'];

if (!is_valid_tracking_number($q)) {
  $url = $error . 'Invalid+Tracking+Number';
}
else {
  $tn = clean_tracking_number($q);
  $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);

  $s = $db->prepare("SELECT url ".
		    "FROM alloc_log, appliances, alloc_numbers ".
		    "WHERE alloc_log.numbers_id = alloc_numbers.id AND ".
		    "      alloc_numbers.name = 'tracking_number' AND ".
		    "      alloc_log.seed = (:tn - alloc_numbers.base) div ".
		    "                       alloc_numbers.leap AND ".
		    "      appliances.id = alloc_log.appliance_id");

  if (!$s) {
    $e = $db->errorInfo();
    $url = $error . $e[2];
  }
  else if (!$s->execute(array("tn" => $tn))) {
    $e = $s->errorInfo();
    $url = $error . $e[2];
  }
  else {
    $row = $s->fetch();

    if ($row)
      $url = $row['url'] . '/secure/trackemail.php?a=' . $tn;
    else
      $url = $error . 'Unknown+Tracking+Number';
  }
}

header("Location: $url");

?>
