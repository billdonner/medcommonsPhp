<?php

/*
 * get_trust_root()
 *
 * Returns a full URL for the directory of this PHP script.
 *
 * Guaranteed to end in '/'
 */
function get_trust_root() {
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $defaultPort = 443;
    $result = 'https://';
  }
  else {
    $defaultPort = 80;
    $result = 'http://';
  }

  $result .= $_SERVER['SERVER_NAME'];

  if ($_SERVER['SERVER_PORT'] != $defaultPort)
    $result .= ':' . $_SERVER['SERVER_PORT'];

  $result .= dirname($_SERVER['REQUEST_URI']);

  if (substr($result, -1) != '/')
    $result .= '/';

  return $result;
}

error_reporting(E_ALL ^ E_WARNING);

/**
 * Require the OpenID consumer code.
 */
require_once "Auth/OpenID/Consumer.php";

/**
 * Require the "file store" module, which we'll need to store OpenID
 * information.
 */
require_once "Auth/OpenID/FileStore.php";

/**
 * This is where the example will store its OpenID information.  You
 * should change this path if you want the example store to be created
 * elsewhere.  After you're done playing with the example script,
 * you'll have to remove this directory manually.
 */
$store_path = file_exists("/tmp") ? "/tmp/_php_consumer_test" : "c:\\temp\\_php_consumer_test";

if (!file_exists($store_path) &&
    !mkdir($store_path)) {
    print "Could not create the FileStore directory '$store_path'. ".
        " Please check the effective permissions.";
    exit(0);
}

$store = new Auth_OpenID_FileStore($store_path);

/**
 * Create a consumer object using the store object created earlier.
 */
$consumer = new Auth_OpenID_Consumer($store);

?>
