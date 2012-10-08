<?php

// payviaccfin.php
//
//	price - the product
//	id - the medcommons account id
//	cc - the credit card selector /nikname

require_once "dbparamspay.inc.php";
require_once "../acct/appsrvlib.inc.php";

require_once "payviacc.inc.php";

list($accid,$fn,$ln,$email,$idp,$cookie) = pconfirm_logged_in (); // does not return if not lo
$db = pconnect_db(); // connect to the right database

$status = purchase(
$price, // product name
$accid,// account id
$cc, // credit card selector
$ret); //where to continue

echo "Failure return from purchase in payviaccfin.php status = $status";
exit;
?>