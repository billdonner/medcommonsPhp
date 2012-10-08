<?php

require 'settings.php';
require 'template.inc.php';
require 'skey.inc.php';

$t = template("edit.tpl.php");
$t->set('Secure_Url', $Secure_Url);

/* pretty mcid: XXXX-XXXX-XXXX-XXXX */
function pretty_mcid($mcid) {
  return substr($mcid, 0, 4) . '-' .
    substr($mcid, 4, 4) . '-' .
    substr($mcid, 8, 4) . '-' .
    substr($mcid, 12);
}

function setup_template($t, $mcid, $row) {
  global $MCRYPT_RAND_SOURCE;

  $t->set('mcid', $mcid);
  $t->set('pretty_mcid', pretty_mcid($mcid));
  $t->set('name', $row['first_name'] . ' ' . $row['last_name']);
  $t->set('email', $row['email']);

  $t->set('since', $row['since']);
  $t->set('ccrlogupdatetime', date('Y-m-d H:i:s', $row['ccrlogupdatetime']));

  $t->set('password', base64_encode(mcrypt_create_iv(6, $MCRYPT_RAND_SOURCE)));
}

try {
  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

  if (isset($_POST['verify'])) {
    /* form submission */
    $mcid = $_POST['mcid'];

    $skey = strtoupper($_POST['skey']);

    $stmt = $db->prepare("SELECT * FROM users WHERE mcid = ?");

    if (!$stmt->execute(array($mcid))) {
      print_r($db->errorInfo());
      exit;
    }

    if ($row = $stmt->fetch()) {
      $next = skey_step(skey_get($skey));

      setup_template($t, $mcid, $row);

      $stmt->closeCursor();

      if ($next == $row['skey']) {
	/* MATCH */

	$stmt = $db->prepare("UPDATE users SET skey = ?, sha1 = ? " .
			     "WHERE mcid = ?");

	$stmt->bindParam(1, skey_get($skey));
	$stmt->bindParam(2, strtoupper(hash('SHA1', 'medcommons.net' . 
					    $mcid . $_POST['password'])));
	$stmt->bindParam(3, $mcid);

	$stmt->execute();

	$t->set('msg', 'Password reset!');
      }
      else {
	/* NO MATCH */
	$t->set('msg', "S/Key doesn't match");
      }
    }
    else {
      /* NO SUCH USER */
      $t->set('msg', 'Account ' . pretty_mcid($mcid) . ' not found');
    }

  }

  else if (isset($_GET['mcid'])) {
    $mcid = $_GET['mcid'];

    $stmt = $db->prepare("SELECT * FROM users WHERE mcid = ?");
    if (!$stmt->execute(array($mcid))) {
      print_r($db->errorInfo());
      die();
    }

    if ($row = $stmt->fetch()) {
      setup_template($t, $mcid, $row);

      $stmt->closeCursor();

      $stmt = $db->prepare("SELECT groupinstances.name, groupinstances.groupinstanceid".
			   " FROM groupinstances, groupmembers" .
			   " WHERE groupmembers.memberaccid = ? AND" .
			   " groupinstances.groupinstanceid = groupmembers.groupinstanceid");

      if (!$stmt->execute(array($mcid))) {
	print_r($db->errorInfo());
	die();
      }

      $t->set('groups', $stmt->fetchAll());

      $stmt->closeCursor();

      // MAJOR ASSUMPTION: you must be a member of a group you're an admin of
      $stmt = $db->prepare("SELECT groupinstanceid FROM groupmembers" .
			   " WHERE memberaccid = ?");

      if (!$stmt->execute(array($mcid))) {
	print_r($db->errorInfo());
	die();
      }

      $admin = array();
      while ($row = $stmt->fetch())
	$admin[(int) $row['groupinstanceid']] = "Yes";

      $t->set('admin', $admin);
    }
    else
      $t->set('msg', 'Account ' . pretty_mcid($mcid) . ' not found');
  }

  echo $t->fetch();
} catch (PDOException $e) {
  print "Error! ";
  print $e->getMessage();
  die();
}
   ?>
