<?php
require_once 'modpay.inc.php';
$c = $_REQUEST['c'];
$next = $_REQUEST['next'];


sql("Update modcoupons set status = 'revoked'  where couponum='$c'  ");
// should check v2 against otp here
header("Location: $next");
die ("redirecting to $next sql status is ".mysql_error());


?>
