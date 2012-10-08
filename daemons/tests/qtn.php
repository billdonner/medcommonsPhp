<?php

require 'settings.php';

if (isset($_GET['q'])) {
  $q = $_GET['q'];

  $tn = join("", explode("-", join("", explode(" ", trim($q)))));

  if (strlen($tn) == 12) {
    $q = substr($tn, 0, 4) . '-' . substr($tn, 4, 4) . '-' . substr($tn, 8, 4);
    $table = 'tracking_number';
  }
  else if (strlen($tn) == 16) {
    $q = substr($tn, 0, 4) . '-' . substr($tn, 4, 4) . '-' . substr($tn, 8, 4) . '-' . substr($tn, 12, 4);
    $table = 'mcid';
  }

  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

  $s = $db->prepare("SELECT id, base, leap FROM alloc_numbers WHERE name='$table'");
  if (!$s->execute()) {
    echo "1?";
    die();
  }

  $row = $s->fetch();
  if (!$row) {
    echo "2?";
    die();
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
	$url = 'https' + substr($url, 4) + '/secure/lookup.php?a=' . $tn;
	echo $url;
	//header('Location: ' . $url);
	die();
      }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
  <head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title></title>
    <link rel='Stylesheet' type='text/css' href='style.css' />
  </head>
  <body>

     <?php echo "$q lives on $name, $url, $email"; ?>

  </body>
</html><?php
      die();
    }
  }
 }
 else
   $q = '';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
  <head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title></title>
    <link rel='Stylesheet' type='text/css' href='style.css' />
  </head>
  <body>

    <form method='get' action='qtn.php'>
      <label>Tracking number:
        <input type='text' name='q' value='<?php echo $q; ?>' /></label>
      <input type='submit' value='Go' />
    </form>

  </body>
</html>
