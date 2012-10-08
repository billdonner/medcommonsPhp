<?php

require 'login.inc.php';
require 'settings.php';
require 'skey.inc.php';
require_once 'mc.inc.php';

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

$mcid = login_required('skey.php');

$s = $db->prepare("SELECT first_name, last_name, enc_skey " .
		  "FROM users WHERE mcid = :mcid");

if ($s->execute(array("mcid" => $mcid))) {
  $row = $s->fetch();

  $pretty_mcid = pretty_mcid($mcid);

  if ($row['enc_skey']) {
  }
  else {
    $t = template($acTemplateFolder . 'receipt.tpl.php');

    $a = array();

    $seed = mcrypt_create_iv(8);

    for ($i = 0; $i < 12; $i++) {
      $seed = skey_step($seed);
      array_push($a, skey_put($seed));
    }

    $seed = skey_step($seed);

    $t->set('skey', $a);

    $s->closeCursor();
    $s = $db->prepare("UPDATE users".
		      " SET enc_skey = :skey".
		      " WHERE mcid = :mcid");
    if (!$s) {
      print_r($db->errorInfo());
      die();
    }

    $s->execute(array("skey" => base64_encode($seed), "mcid" => $mcid));

    $t->set('first_name', $row['first_name']);
    $t->set('last_name', $row['last_name']);
    $t->set('mcid', $pretty_mcid);

    echo $t->fetch();
  } 
}

?>
