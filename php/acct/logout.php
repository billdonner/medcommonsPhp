<?
/*
 * This file is a wrapper for the real logout which belongs to the 'account' services
 * and thus *may* reside on a different server.
 *
 * wld - hacked this to use a form and take us to the top target frame
 * ttw - replaces the identity server's logout... go through a list of cookies to clear,
 *       then redirect to the home page
 */

include 'settings.php';
include 'urls.inc.php';

session_start();

if (isset($_COOKIE['mc'])) {
  if ($acCookieDomain && $acCookieDomain != 'localhost' )
    setcookie('mc', False, 1, '/', $acCookieDomain);
  else
    setcookie('mc', False, 1, '/');
}

setcookie('mode', False, 1, '/');

if (isset($_COOKIE['mc_anon_auth'])) {
  if ($acCookieDomain && $acCookieDomain != 'localhost' )
    setcookie('mc_anon_auth', False, 1, '/', $acCookieDomain);
  else
    setcookie('mc_anon_auth', False, 1, '/');
}

// Destroy the session variables
session_destroy(); 

header('Location: ' . $acHomePage);
?><html>
 <head>
  <meta http-equiv='Location' content='<?= $acHomePage ?>' />
 </head>
 <body>
<a href='<?= $acHomePage ?>'><?= $acHomePage ?></a>
 </body>
</html>
