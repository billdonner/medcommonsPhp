<?php 

require_once "setup.inc.php";

function logon_redirect($returl, $err)
{
	$url = $returl;
	$x=<<<XXX
<html><head><title>Verisign Purchase completed</title>
</HEAD><body>Thank you for your payment. Please dismiss  this window</body>
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
$product = $_POST['USER5'];
$partner = $_POST['USER6'];
$xid = $_POST['USER7'];
$callerip = $_POST['USER8'];
$pnref= $_POST['PNREF'];
$result = $_POST['RESULT'];
$avsdata = $_POST ['AVSDATA'];
$respmsg = $_POST ['RESPMSG'];
$authcode = $_POST ['AUTHCODE'];
//amount is filled in above
$amount = $vprice; //100*$amount; // user unscaled amount

/*
mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

// now write an entry in the mysql database

$insert="INSERT INTO ccreturn (time,custid,vprice,returl,accid,nowtime,product,partner,xid,callerip,pnref,result,avsdata,respmsg,authcode)
		VALUES(".
"NOW(),'$custid','$vprice','$returl','$accid','$nowtime','$product','$partner','$xid','$callerip','$pnref','$result','$avsdata',
	           '$respmsg','$authcode')";

mysql_query($insert) or die("can not insert into table ccreturn - ".mysql_error());

mysql_close();


islog('Return',$callerip,"xid $xid avsdata $avsdata custid $custid  pnref $pnref respmsg $respmsg amount $amount");
logon_redirect ($returl,"");
*/

	$x=<<<XXX
<html><head><title>Purchase of $product Nearly Complete</title>
</HEAD><body>
<img src='http://www.medcommons.net/images/smallwhitelogo.gif'>
<p>We have successfully sent your payment of $vprice to our card processor. </p>
<p>Please dismiss  this window or <a href=pay.php?html=30&xid=$xid >check payment status</a></p></body>
</html>
XXX;
	echo $x;
?>