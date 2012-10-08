<?php

require 'is.inc.php';
require_once 'common.php';
if (isset($GLOBALS['openid_hack']))
{
	$openid = $GLOBALS['openid_hack'];
	setcookie('u',urlencode($openid)); // setup a simple cookie to remember where we are, expire in 30 days
	
	$url = "is.php?openidhack=$openid";
} else {
session_start();

$openid_url = trim($_POST['openid_url']);
$trust_root = get_trust_root();

$auth_request = $consumer->begin($openid_url);

if (!$auth_request)
  $url = 'index.php?err=OpenID+Authentication+Error';
else
  $url = $auth_request->redirectURL($trust_root, $trust_root . 'is.php');

}
redirect($url);

?>
