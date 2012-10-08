<?php
require_once "dbparamsidentity.inc.php";
require_once "consent_support.inc.php";
require_once "utils.inc.php";
require_once "JSON.php";

nocache();

try {
  $accid = req("accid");

  if(!mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password']))
    throw new Exception("can not connect to mysql");

  $db = $GLOBALS['DB_Database'];
  if(!mysql_select_db($db)) 
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
  $result->message = $msg;
  echo $json->encode($result);
}
?>
