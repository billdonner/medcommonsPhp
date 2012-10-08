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

if (!is_valid_mcid($q)) {
  $url = $error . "Invalid+MCID";
}
else {
  $mcid = clean_mcid($q);
  $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);

  $sql = "SELECT url ".
    "FROM alloc_log, appliances, alloc_numbers ".
    "WHERE alloc_log.numbers_id = alloc_numbers.id AND ".
    "      alloc_numbers.name = 'mcid' AND ".
    "      alloc_log.seed = ($mcid - alloc_numbers.base) div ".
    "                       alloc_numbers.leap AND ".
    "      appliances.id = alloc_log.appliance_id";

  $s = $db->prepare($sql);

  if (!$s) {
    $e = $db->errorInfo();
    $url = $error . $e[2];
  }
  else if (!$s->execute()) {
    $e = $s->errorInfo();
    $url = $error . $e[2];
  }
  else {
    $row = $s->fetch();

    if ($row)
      $url = $row['url'] . '/acct/login.php?mcid=' . pretty_mcid($mcid);
    else
      $url = $error . "Unknown+MCID";
  }
}

header("Location: $url");

?>
