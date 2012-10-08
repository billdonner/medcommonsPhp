<?php
require_once "settings.php";
require_once "consent_support.inc.php";
require_once "utils.inc.php";
require_once "JSON.php";

nocache();

try {
  $accid = req("accid");

  if(!mysql_connect("$IDENTITY_HOST", $IDENTITY_USER, $IDENTITY_PASS))
    throw new Exception("can not connect to mysql");

  if(!mysql_select_db($IDENTITY_DB)) 
    throw new Exception("can not connect to database $db");

  $rights = get_sharing_info($accid);
  mysql_close();

  $json = new Services_JSON();
  $obj = new stdClass;
  $obj->status = "ok";
  $obj->shares = $rights;

  // Because JSON encode sometimes emits warnings in the middle of output!
  error_reporting(0);
  echo $json->encode($obj);
}
catch(Exception $e) {
  $json = new Services_JSON();
  $result = new stdClass;
  $result->status = "failed";
  $result->message = $e->getMessage();
  echo $json->encode($result);
}
?>
