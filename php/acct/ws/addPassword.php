<?php

/*
 * Web service to add a password to an account that doesn't yet
 * have an account.
 *
 * Input:
 *     POST /acct/ws/addPassword.php
 *      form data:
 *       mcid=XXXX-XXXX-XXXX-XXXX
 *       password=text
 *
 * Output:
 *     A query string containing either:
 *       status=OK
 *     or
 *       status=ERR&msg=Error message
 */
require 'settings.php';
require 'mc.inc.php';

$demo_mcids = array('1012576340589251', '1013062431111407', '1035582511657478',
		    '1082155036018986', '1087997704966332', '1088448116240388',
		    '1106558614028065', '1117658438174637', '1135142322127072',
		    '1162164444007929', '1166439538173659', '1172619833385984',
		    '1175376381039160', '1192791741379853', '1259366818364933');

if (isset($_POST['mcid']) && isset($_POST['password'])) {
  $mcid = $_POST['mcid'];

  if (!is_valid_mcid($mcid)) {
    $error = 'Invalid MCID';
  }
  else {
    $mcid = clean_mcid($mcid);
    $sha1 = strtoupper(hash('SHA1', 'medcommons.net' . $mcid . $_POST['password']));

    $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);

    $s = $db->prepare("UPDATE users SET sha1 = :sha1 WHERE sha1 IS NULL and mcid = :mcid");

    if (!$s) {
      $e = $db->errorInfo();
      $error = $e[2];
    }
    else if (!$s->execute(array("sha1" => $sha1, "mcid" => $mcid))) {
      $e = $s->errorInfo();
      $error = $e[2];
    }
    else {
      echo 'status=OK';
      exit;
    }
  }

  echo 'status=ERR&msg=' . $error;
}
else {
?><html>
  <body>
    <form method='post' action='addPassword.php'>
      <input type='text' name='mcid' />
      <input type='password' name='password' />
      <input type='submit' />
    </form>
  </body>
</html><?php } ?>
