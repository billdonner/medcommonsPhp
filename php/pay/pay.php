<?php

require_once "setup.inc.php";

function doprettyprice ($price)
{
	$dollars = intval($price/100);
	$cents = $price - $dollars*100;
	$tens = intval($cents/10);
	$ones = $cents -$tens*10;
	$vprice = "$".$dollars.".".$tens.$ones;
	return $vprice;
}
function onwhitelist($ip)
{
	if ($ip=="0123456789A") return true;
 
	return false;
}
/// really starts here

if (isset($_GET['xid']))
{
	$xid = $_GET['xid'];
	$result = dosql ("Select respmsg,authcode from ccstatus where xid='$xid' ");
	$r = mysql_fetch_object($result);
	if (isset($_GET['html']))
	{
		$sleepsecs = $_GET['html'];
		echo "
		<html><heald><title>MedCommons Payment Services Status for $xid</title>
		<meta http-equiv='Refresh' content='$sleepsecs' /></head><body>
		";
		if ($r===false) echo  ("xid $xid not found</body></html>");
		else echo  ("respmsg: $r->respmsg authcode: $r->authcode </body></html>");
		exit;		
	}
	header("Content-type: text/xml");
	if ($r===false) echo  ("<?xml version='1.0' encoding='UTF-8'?>
	<payment-status>
	<respmsg>failure</respmsg>
	</payment-status>");
	else
	echo "<?xml version='1.0' encoding='UTF-8'?>
	<payment-status>
	<respmsg>$r->respmsg</respmsg>
	<authcode>$r->authcode</authcode>
	</payment-status>";
	exit;
}
else 

{
$callerip = "0123456789A";
$on = onwhitelist($callerip)? 'true' : 'false';
//echo "Caller ip is $callerip onwhitelist $on";
if (!onwhitelist($callerip)){
	islog('notonwhitelist',$callerip,'Not on whitelist');
	header("Content-type: text/xml");
	echo  ("<?xml version='1.0' encoding='UTF-8'?>
	<payment-status>
	<respmsg>$callerip not on whitelist, please contact appliance owner</respmsg>
	</payment-status>");
	exit;
}
// if POSTING start here, there's a ton of required parameters
$price = $_POST['price'];
$product = $_POST['product'];
$partnerid = $_POST['partner'];
$accid = $_POST['accid'];
$xid = $_POST['xid']; // transaction id, should be sha1 like unique
$returl = $_POST['returl'];
$custid = $_POST['custid'];

$vprice = doprettyprice($price);
$ccprice = $price/100.0;
$nowtime = time();
$action = "https://payments.verisign.com/payflowlink";
// and here are the optional ones
$optional ='';
if (isset($_POST['NAME'])) $optional.="<input type='hidden' value='".$_POST['NAME']."' name='NAME' />";
if (isset($_POST['ADDRESS'])) $optional.="<input type='hidden' value='".$_POST['ADDRESS']."' name='ADDRESS' />";
if (isset($_POST['CITY'])) $optional.="<input type='hidden' value='".$_POST['CITY']."' name='CITY' />";
if (isset($_POST['STATE'])) $optional.="<input type='hidden' value='".$_POST['STATE']."' name='STATE' />";
if (isset($_POST['ZIP'])) $optional.="<input type='hidden' value='".$_POST['ZIP']."' name='ZIP' />";
if (isset($_POST['CARDNUM'])) $optional.="<input type='hidden' value='".$_POST['CARDNUM']."' name='CARDNUM' />";
if (isset($_POST['EXPDATE'])) $optional.="<input type='hidden' value='".$_POST['EXPDATE']."' name='EXPDATE' />";
// build the message for verisign
$digs11 = "99999999999";

$callerip =$_SERVER['REMOTE_ADDR'];

$x=<<<XXX
<HTML>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
 Copyright 2008 MedCommons Inc.   All Rights Reserved.
-->
  <head>
    <title>MedCommons Payflow Payment</title>
     <meta name="robots" content="none">
    <script language="javascript">
      function init() {
      foo.submit();
      }
    </script>
  </head>
<BODY onload="init()">
<img src='http://www.medcommons.net/images/smallwhitelogo.gif'>
<p>Please wait while we contact our car processor...</p>
<form method="POST"   name = "foo" id = "foo" action="$action">
<input type="hidden" name="LOGIN" value="medcommons">
<input type="hidden" name="PARTNER" value="VeriSign">
<input type="hidden" name="AMOUNT" value="$ccprice">
<input type="hidden" name="TYPE" value="S">
<input type="hidden" name="DESCRIPTION" value="MedCommons Account $accid Payment for $product from $partnerid ($xid) " >
<input type="hidden" name="USER1" size="16" value="$vprice">
<input type="hidden" name="USER2"  value="$returl&xid=$xid">
<input type="hidden" name="USER3"  value="$accid">
<input type="hidden" name="USER4"  value="$nowtime">
<input type="hidden" name="USER5"  value="$product">
<input type="hidden" name="USER6"  value="$partnerid">
<input type="hidden" name="USER7"  value="$xid">
<input type="hidden" name="USER8"  value="$callerip">
<input type="hidden" name="CUSTID" value="$custid">
<input type="hidden" name = "SHOWCONFIRM" value="False">
<input type="hidden" name = "ECHODATA" value="True">
$optional
</form>
</body>
</html>
XXX;
echo $x;

	islog('Posted',$callerip,"$accid $vprice $product $partnerid ($xid)");
}


?>