<?php

// this is a hacked version of glib.inc.php because I cant figure out the nesting structure of dbparams, etc.

$GLOBALS['RLS_Name'] = "MedCommons Builtin Registry";
$GLOBALS['RLS_Version'] = "0.2";
$GLOBALS['RLS_DB']="groupccrevents";
if (isset($GLOBALS['Default_Repository']))
$GLOBALS['RLS_Default_Repository'] = $GLOBALS['Default_Repository']; // this should be computed
else $GLOBALS['RLS_Default_Repository'] ='';

require_once "dbparamsidentity.inc.php";
//require_once "../glibint.inc.php";
?>
