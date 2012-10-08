<?php

require 'login.inc.php';
require 'settings.php';
require 'mc.inc.php';
require 'OpenID.php';
require_once 'utils.inc.php';
require_once 'demodata_ids.inc.php';

nocache();

$t = new Template();

$t->set('acOnlineRegistration', $acOnlineRegistration);

// Used for auto-accessing demo accounts
if(isset($_GET['access_accid'])) {
  $accessId = $_GET['access_accid'];
  dbg("got access id $accessId");
  if($accessId == $janesId) {
    dbg("accessId == jane");
    $_REQUEST['openid_url'] = "jhernandez@medcommons.net";
    $t->set('demo_password', "tester");
  }
}

function login($row) {
  global $next, $t;

  $mcid = $row['mcid'];
  $token = get_authentication_token($mcid, $t);
  $user = User::load($mcid);
  $user->authToken = $token;

  if ($row['acctype'] == 'CLAIMED')
    $user->login('claim.php?next=' . urlencode($next));
  else
    $user->login($next);
}

/**
 * Redirects a user to a page authenticated by a phone number
 * and access code.
 */
function handle_phone_number($phoneNumber, $next, &$t) {

  $t->esc("openid_url",$phoneNumber);

  // If no password, allow to fall through to login page
  if(!isset($_POST['password']))  
    return;

  $password = $_POST['password'];

  // Get phone free of unwanted separator characters (-, . space, etc.)
  $phoneNumber = preg_replace("/[ -\.]/", "", $phoneNumber);

  // Check against phone number table
  require_once "alib.inc.php";
  $matches = pdo_query("select * from phone_authentication
                        where pa_phone_number = ?
                        and pa_access_code = ?",
                       array($phoneNumber, $password));

  if(count($matches) === 0) {
    $t->set('error', 'No such user/bad password');
    return;
  }

  // Login as external identity
  $url = gpath('Commons_Url')."/ws/createAuthenticationToken.php?accountIds=tel:1".urlencode($phoneNumber);
  $json = get_url($url);
  $decoder = new Services_JSON();
  $result = $decoder->decode($json);
  if(!$result || ($result->status !== "ok"))
  error_page("Internal System Error creating Authentication Token",
                 "Invalid result returned when creating auth token url=$url result=$json");

  $token = $result->result;
  dbg("Got auth token $token for validated phone number $phoneNumber");

  // Set the anonymous auth cookie
  header('Set-Cookie: mc_anon_auth='.$token.'; path=/');

  // If the user was on their way to a known HealthURL, just forward them
  if(preg_match("/.*\/acct\/cccrredir.php\?.*\&accid=[0-9]{16}.*$/",$next) === 0) 
    $next  = gpath('Commons_Url')."/phoneredir.php";

  // In this case we send them over to /secure to look up the most recent
  // phone entry
  dbg("next = $next");
  redirect($next);
}

function handle_openid($openid_url, $next, &$t) {

    require 'common.php';

    global $IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS;

    dbg("Handling id $openid_url as openid");

    $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

    session_start();

    $t->esc('openid_url', $openid_url);
    $t->set('password', False);

    if(isset($_POST['idptype']) && ($_POST['idptype'] === 'otheridp')) {
      $mc_idp = 'otheridp';
      $mc_source_id = 'OpenID Provider';
    }
    else {
      $mc_idp = False;
      $mc_source_id = False;

      $stmt = $db->prepare("SELECT id, format, name FROM identity_providers");
      $result = $stmt->execute();

      if ($result) {
        while ($row = $stmt->fetch()) {
          if (match_openid($openid_url, $row['format'])) {
            /*
             * Found the whitelisted identity provider
             */
            $mc_idp = $row['id'];
            $mc_source_id = $row['name'];
            break;
          }
        }
      }
    }

    $allow_anon_openid = false;
    if(isset($_POST['allow_anon_openid'])) {
      $_SESSION['mc_allow_anon_openid'] = $_POST['allow_anon_openid'];
      $allow_anon_openid = ($_POST['allow_anon_openid']=="true");
    }

    if(!$mc_idp && !$allow_anon_openid) {
      dbg("no matching idp found");
      $t->esc('error', 'OpenID provider not whitelisted on this system');
      echo $t->fetch('login.tpl.php');
      exit;
    }

    $_SESSION['mc_next'] = $next;
    $_SESSION['mc_idp'] = $mc_idp;
    $_SESSION['mc_source_id'] = $mc_source_id;

    $scheme = 'http';

    if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
      $defaultPort = 443;
      $scheme .= 's';
    }
    else {
      $defaultPort = 80;
    }

    $process_url = combine_urls(get_request_url(), "auth.php");
    $trust_root = $scheme . '://' . $_SERVER['SERVER_NAME'];

    if ($_SERVER['SERVER_PORT'] != $defaultPort)
      $trust_root .= ':' . $_SERVER['SERVER_PORT'];

    $trust_root .= dirname($_SERVER['PHP_SELF']);

    $auth_request = $consumer->begin($openid_url);
 
    if (!$auth_request) {
      $t->set('error', 'OpenID Authentication Error');
    }
    else {
      $redirect_url = $auth_request->redirectURL($trust_root, $process_url);

      redirect($redirect_url);
    }
}
 
if (isset($_POST['next']))
  $next = $_POST['next'];
else if (isset($_GET['next']))
  $next = $_GET['next'];
else 
  $next = '/acct/home.php';

if(isset($next) && (!$next || ($next ==="")))
  $next = '/acct/home.php';

$t->esc('next', $next);
$t->set('password', False);

/*
 * if it's a complete POST request, must contain valid email and
 * matching passwords.  If valid, then *redirect* with cookie to
 * correct user page.  If not valid, display template with error
 * inserts.
 */

if (isset($_REQUEST['openid_url']))
    $openid_url = trim($_REQUEST['openid_url']);
else if (isset($_REQUEST['mcid']))
    $openid_url = trim($_REQUEST['mcid']);
else if (isset($_REQUEST['email']))
    $openid_url = trim($_REQUEST['email']);
else
    $openid_url = False;

if ($openid_url) {
  $t->set('password', True);

  /* OpenID? */
  if (is_openid_url($openid_url)) {
    handle_openid($openid_url, $next, $t);
  }
  else if(is_phone_number($openid_url)) {
    handle_phone_number($openid_url, $next, $t);
  }
  else if (is_valid_mcid($openid_url)) {

    $mcid = clean_mcid($openid_url);

    /* hey! we're already logged in! */
    if ($mcid == get_login_mcid())
        redirect('/acct/home.php');

    $t->esc('openid_url', pretty_mcid($mcid));

    if (isset($_POST['password'])) {
      $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);
      $password = $_POST['password'];

      $sha1 = User::compute_password($mcid, $password);

      $sql = <<<EOF
SELECT users.email, users.mcid,
       users.first_name, users.last_name, users.acctype
FROM   users
WHERE  users.mcid = :mcid AND
       users.sha1 = :sha1
EOF;

      $stmt = $db->prepare($sql);
      $result = $stmt->execute(array("mcid" => $mcid, "sha1" => $sha1));

      if ($result) {
        $row = $stmt->fetch();
        if ($row) {
          $stmt->closeCursor();
          login($row);
        }
      }
      $t->set('error', 'No such user/bad password');

    }
  }
  else if (is_email_address($openid_url)) {
    $t->esc('openid_url', $openid_url);

    if (isset($_POST['password'])) {
      $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

      $email = $openid_url;
      $password = $_POST['password'];

      $sql = <<<EOF
SELECT users.mcid, users.sha1,
       users.first_name, users.last_name,
       users.email, users.acctype
FROM users
WHERE users.email = :email
ORDER BY users.since desc
EOF;

      $stmt = $db->prepare($sql);
      $result = $stmt->execute(array("email" => $email));

      if ($result) {
        while ($row = $stmt->fetch()) {
          $sha1 = User::compute_password($row['mcid'], $password);
          if ($row['sha1'] == $sha1) {
            $stmt->closeCursor();
            login($row);
          }
        }
      }
      $t->set('error', 'No such user/bad password');
    }
  }
  else {
    $t->esc('openid_url', $openid_url);
  }
}
else {
  $t->esc('openid_url', '');
}

echo $t->fetch("${acTemplateFolder}login.tpl.php");

?>
