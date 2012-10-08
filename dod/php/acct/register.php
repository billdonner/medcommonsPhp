<?php

require_once 'template.inc.php';
require_once 'login.inc.php';
require_once 'settings.php';
require_once 'verify.inc.php';
require_once 'utils.inc.php';

nocache();

/*
if (! $acOnlineRegistration) {
  $t = template($acTemplateFolder . 'login.tpl.php');
  $t->set('acOnlineRegistration', $acOnlineRegistration);
  $t->set('mcid', '');
  $t->set('error', 'Only sponsored registrations are currently supported');
  echo $t->fetch();
  exit;
 }
*/
/*******************************
 * MCID allocation using SOAP...
 */
function get_mcid() {
  global $URL, $NS;

  $client = new SoapClient(null, array('location' => $URL, 'uri' => $NS));
  return $client->next_mcid();
}
/*
 * ...MCID allocation using SOAP
 *******************************/

/**
 * Activate the given MedCommons account using the specified
 * activation key.
 */
function activateAccount($mcid, $activationKey) {
  dbg('Activating account '.$mcid.' using key '.$activationKey);
  global $IDENTITY_WS_URL, $IDENTITY_WS_NS;
  $client = new SoapClient(null, array('location' => $IDENTITY_WS_URL, 'uri' => $IDENTITY_WS_NS));
  return $client->activate($mcid, $activationKey, "");
}

/**
 * Check if the config value indicating that this is a 
 * public appliance is set and if so, enable the account
 * for public access.
 *
 * @return - true if successful, false otherwise
 */
function check_enable_public_access($mcid) {
  global $acPublicAppliance;
  if(isset($acPublicAppliance) && ($acPublicAppliance=='true')) { // It's a public appliance!
    dbg("Enabling public access for account $mcid");
    $result = file_get_contents(gpath('Commons_Url')."/ws/grantAccountAccess.php?accessBy=0000000000000000&accessTo=".$mcid."&rights=R");
    if(preg_match("%<status>ok</status>%",$result)===false) {
      dbg("Failed to enable public access for account $mcid: ".$result);
      return false;
    }
  }
  return true;
}

$MIN_PW_LEN = 6;

if (isset($_GET['layout']) && $_GET['layout'] == "none") {
  $t = template('register.tpl.php');
  $layout = template('widget.tpl.php')->nest("content", $t)->set("title", "Register a New Account");
}
else {
  $t = template('register.tpl.php');
  $layout = template('base.tpl.php')->nest("content", $t)->set("title", "Register a New Account");
}

if (isset($_POST['next']))
  $next = $_POST['next'];
else if (isset($_GET['next']))
  $next = $_GET['next'];
else
  $next = 'home.php';

$t->esc('next', $next);

$activationKey = req('ActivationKey',req('ak'));
if($activationKey) {
  $t->set('activationKey', $activationKey);
}

/*
 * if it's a complete POST request, must contain valid email and
 * matching passwords.  If valid, then *redirect* to correct login
 * page.  If not valid, display template with error inserts.
 */
if (count($_POST) == 0 || !isset($_POST['pw1'])) {
  if (isset($_GET['email']))
    $email = $_GET['email'];
  else
    $email = '';

  $t->esc('email', $email);
  $t->esc('ln', '');
  $t->esc('fn', '');

  echo $layout->fetch();
}
else {
  $email = trim($_POST['email']);
  $pw1 = $_POST['pw1'];
  $pw2 = $_POST['pw2'];
  $fn = $_POST['fn'];
  $ln = $_POST['ln'];
  $tou = isset($_POST['termsOfUse']) && $_POST['termsOfUse'];

  $errors = 0;

  /* validation */
    if ($tou!='on') {
    $errors++;
    $t->set('tou_error', 'Please confirm you agree with our Terms of Use');
  }
  
  if (!is_valid_email($email)) {
    $errors++;
    $t->set('email_error', 'Please enter a valid email address');
  }

  if (strlen($pw1) < $MIN_PW_LEN) {
    $errors++;
    $t->set('pw1_error', "Passwords must be at least $MIN_PW_LEN characters");
  }

  if ($pw1 != $pw2) {
    $errors++;
    $t->set('pw2_error', 'Passwords must match');
  }

  $t->esc('email', $email);
  $t->esc('ln', $ln);
  $t->esc('fn', $fn);

  if ($errors > 0) {
    echo $layout->fetch();
  }
  else {

    /*
     * We have a validated email address, not yet confirmed,
     * and we have a valid password.  Let's do the register.
     */

    $mcid = get_mcid();

    dbg("registering user $mcid");

    $sha1 = User::compute_password($mcid,$pw1);

    /*
    $pk = openssl_pkey_new();
    $pkstr = '';
    openssl_pkey_export($pk, $pkstr, $pw1);

    $csr = openssl_csr_new(array("CN" => $mcid,
				 "O" => $acCommonName,
				 "GN" => $fn,
				 "SN" => $ln), $pk);

    $crt = openssl_csr_sign($csr, NULL, $pk, 365);

    $crtstr = '';
    openssl_x509_export($crt, $crtstr);

    openssl_pkey_free($pk);
    openssl_x509_free($crt);
     */

    $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
		  $DB_SETTINGS);

    $stmt = $db->prepare("INSERT INTO users (".
			 " mcid, first_name, last_name,".
		       //" private_key, certificate,".
			 " sha1, server_id, acctype".
			 ") VALUES (".
			 " :mcid, :fn, :ln," .
		       //" :pkstr, :crtstr,".
			 " :sha1, 1, 'USER')");

    if (!$stmt->execute(array("mcid" => $mcid,
			      "fn" => $fn,
			      "ln" => $ln,
			   // "pkstr" => $pkstr,
			   // "crtstr" => $crtstr,
			      "sha1" => $sha1))) {
      $e = $stmt->errorInfo();
      $t->esc('db_error', $e[2]);
      echo $layout->fetch();
    }
    else {

      // if activation key specified, activate the account
      if($activationKey) {
        activateAccount($mcid, $activationKey);
      }
      else {
        dbg("No activation key provided for account $mcid");
      }

      /* redirect to new page */
      if(check_enable_public_access($mcid) !== true) {
        $t->esc('db_error', 'failed to enable public access for this account');
        echo $layout->fetch();
      }
      else {
        verify_new_email($mcid, $email);

        $user = User::load($mcid);
        $user->authToken = get_authentication_token($mcid,$t);
        if($user->authToken === false)
          echo $layout->fetch();
        else
          $user->login($next);
      }
    }
  }
}

?>
