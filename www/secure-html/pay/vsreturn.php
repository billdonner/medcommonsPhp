<?php 

require_once "dbparamspay.inc.php";

function logon_redirect($returl, $err)
{
	$url = $returl;
	$x=<<<XXX
<html><head><title>Verisign Purchase completed</title>
<meta http-equiv="REFRESH" content="0;url=$url"></HEAD>
</html>
XXX;
	echo $x;
	exit;
}

$custid=$_POST['CUSTID'];
$vprice = $_POST['USER1'];
$returl = $_POST['USER2'];
$accid = $_POST['USER3'];
$nowtime = $_POST['USER4'];
$expiretime = $_POST['USER5'];
$pnref= $_POST['PNREF'];
$result = $_POST['RESULT'];
$avsdata = $_POST ['AVSDATA'];
$respmsg = $_POST ['RESPMSG'];
$authcode = $_POST ['AUTHCODE'];
//amount is filled in above
$amount = $vprice; //100*$amount; // user unscaled amount

require_once "../acct/appsrvlib.inc.php";
//echo "Add app event paidbill $amount";
$appserviceid = '1234567890';
addAppEvent($accid,$appserviceid,"paidbill",-$amount);


if ($result == 0) logon_redirect ($returl,"Purchase $sku into $accid at $nowtime expires $expiretime complete");
else logon_redirect ($returl,"Error processing your credit card  - $respmsg")
?>