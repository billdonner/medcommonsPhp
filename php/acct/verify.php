<?php

require 'login.inc.php';
require 'urls.inc.php';
require 'verify.inc.php';

require 'settings.php';
require 'skey.inc.php';
require_once 'mc.inc.php';

$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

if (isset($_GET['mcid']) && isset($_GET['hmac']) && isset($_GET['email'])) {
  $mcid = clean_mcid($_GET['mcid']);
  $email = $_GET['email'];
  $hmac = hash_hmac('SHA1', $mcid . $email, $SECRET);

  if ($hmac == $_GET['hmac']) {
    $s = $db->prepare("SELECT first_name, last_name, enc_skey " .
                      "FROM users WHERE mcid = :mcid");

    if ($s->execute(array("mcid" => $mcid))) {
      $row = $s->fetch();

      $pretty_mcid = pretty_mcid($mcid);

      if ($row['enc_skey']) {
        $t = template($acTemplateFolder . 'verified.tpl.php');

        $sql = "UPDATE users".
               " SET email = :email, email_verified = NOW()".
               " WHERE mcid = :mcid";

        $params = array("email" => $email, "mcid" => $mcid);
      }
      else {
        $t = template($acTemplateFolder . 'receipt.tpl.php');

        $a = array();

        $seed = mcrypt_create_iv(8);

        $a = array();

        for ($i = 0; $i < 12; $i++) {
          $seed = skey_step($seed);
          array_push($a, skey_put($seed));
        }

        $seed = skey_step($seed);

        $sql = "UPDATE users".
               " SET email = :email, email_verified = NOW(), enc_skey = :skey".
               " WHERE mcid = :mcid";

        $params = array("email" => $email, "skey" => base64_encode($seed), "mcid" => $mcid);

        $t->set('skey', $a)->esc('email',$email);
      }

      file_get_contents($acGlobalsRoot . 'login/register.php?name='.
                        urlencode($email) . '&mcid=' . $mcid);

      $s->closeCursor();
      $s = $db->prepare($sql);
      if (!$s) {
        print_r($db->errorInfo());
        die();
      }

      $s->execute($params);

      global $acDomain;

      $t->set('first_name', $row['first_name']);
      $t->set('last_name', $row['last_name']);
      $t->set('email', $email);
      $t->set('mcid', $pretty_mcid);
      $t->set('domain',$acDomain);

      echo $t->fetch();
    }
  } 
}

?>
