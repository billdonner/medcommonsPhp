<?php

require 'login.inc.php';
require 'settings.php';
require 'mc.inc.php';

$t = new Template();

$t->set('acOnlineRegistration', $acOnlineRegistration);

function login($mcid) {
  global $next, $t;

  $token = get_authentication_token($mcid, $t);
  $user = User::load($mcid);
  $user->authToken = $token;

  $user->login($next);
}
 
if (isset($_POST['next']))
  $next = $_POST['next'];
else if (isset($_GET['next']))
  $next = $_GET['next'];
else
  $next = '/acct/home.php';

$t->esc('next', $next);
$t->set('password', False);

/*
 * if it's a complete POST request, must contain valid email and
 * matching passwords.  If valid, then *redirect* with cookie to
 * correct user page.  If not valid, display template with error
 * inserts.
 */

$user = trim($_REQUEST['user']);
$mcid = clean_mcid($_REQUEST['mcid']);


if (isset($_POST['password'])) {
  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);
  $password = $_POST['password'];

  $sql = <<<EOF
SELECT password
FROM   auth_user
WHERE  username = :username
EOF;

  $stmt = $db->prepare($sql);
  $result = $stmt->execute(array("username" => $user));

  if ($result) {
    $row = $stmt->fetch();
    if ($row) {
      list($pw_method, $pw_salt, $pw_hash) = explode('$', $row['password']);

      $hash = hash($pw_method, $pw_salt . $password);

      if ($hash == $pw_hash) {
        $stmt->closeCursor();
        login($mcid);
      }
    }
  }

  $t->set('error', 'No such user/bad password');
}

echo $t->fetch("${acTemplateFolder}login.tpl.php");

?>
