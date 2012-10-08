<?php

/*
standard openid authentication interaction

if successfully logged on, go to main.php 

*/

require_once 'common.php';

session_start();

$openid_url = trim($_POST['openid_url']);
$trust_root = get_trust_root();

$auth_request = $consumer->begin($openid_url);

if (!$auth_request)
  $url = 'index.php?err=OpenID+Authentication+Error';
else
  $url = $auth_request->redirectURL($trust_root, $trust_root . 'main.php');

header("Location: $url");

?>
