<?php

function repost ()
{
function doprettyprice ($price)
{
	$dollars = intval($price/100);
	$cents = $price - $dollars*100;
	$tens = intval($cents/10);
	$ones = $cents -$tens*10;
	$vprice = "$".$dollars.".".$tens.$ones;
	return $vprice;
}
	// if POSTING start here, there's a ton of required parameters
$price = $_POST['price'];
$product = $_POST['product'];
$partnerid = $_POST['partner'];
$accid = $_POST['accid'];
$xid = $_POST['xid']; // transaction id, should be sha1 like unique
$returl = $_POST['returl'];

$ptime = strftime('%T');

$custid ="0123456789A"; // compute this based on whitelist at most 11 chars
$vprice = doprettyprice($price);
$ccprice = $price/100.0;
$nowtime = time();
$action = "https://tenth.medcommons.net/pay/pay.php";
$blurb = "<p>$ptime: In a separate window we are initiating the purchase of <br/>
Product $product<br/>
Via a MedCommons Appliance run by $partnerid<br/>
Price $vprice <br/>
MedCommons Account $accid<br/>
The xid is $xid<br/> ";
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

$x=<<<XXX
<HTML>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
 Copyright 2008 MedCommons Inc.   All Rights Reserved.
-->
  <head>
    <title>MedCommons Payflow Payment V 2</title>
     <meta name="robots" content="none">
    <script language="javascript">
      function init() {
      foo.submit();
      }
    </script>
  </head>
<BODY onload="init()">
<img src='http://www.medcommons.net/images/smallwhitelogo.gif'>
$blurb
<form method="POST" target="_new" name = "foo" id = "foo" action="$action">
<input type="hidden" name="price" value="$price">
<input type="hidden" name="returl"  value="$returl&xid=$xid">
<input type="hidden" name="accid"  value="$accid">
<input type="hidden" name="product"  value="$product">
<input type="hidden" name="partner"  value="$partnerid">
<input type="hidden" name="xid"  value="$xid">
<input type="hidden" name="custid" value="$custid">
$optional
</form>
<p>
<a href=$action?html=30&xid=$xid>Check status for $xid</a><br/>
<a href=testpay.php?back>Back to Make Another Test Payment</a><br/>
</p>
</body>
</html>
XXX;
echo $x;
exit;

}

// really start here
$self = $_SERVER['REMOTE_ADDR'];

$time = time();
$xid = sha1($time.$self);
if (isset($_REQUEST['xid']))
{
	repost();
	exit;
}

 else
$form = <<<XXX
<h5>This will put a real payment thru the MedCommons Payments system, so be careful !</h5>
<p>Test MedCommons Payments</p>
<ul>
<li>Fill out this form. Sorry, you really must supply all parameters </li>
<li>This form will take you to Verisign Payment Processing in a new  window</li>
<li>Enter the CSC Code from your card</li>
<li>Copy down the xid you were assigned, if you care</li>
<li>After paying, dismiss the window by closing it</li>
</ul>
<p>xid will be $xid</p>
<form method=post action='testpay.php' >
<input type=text name=partner value='Joes Imaging Shack' /> partnerid<br/>
<input type=text name=accid value='2938291974720183' /> medcommons account id<br/>
<input type=text name=product value='Transfer of 4GB DICOM' /> product <br/>
<input type=text name=price  value=113 /> price (in pennies)<br/>
<input type=text name=NAME value='' /> name on card<br/>
<input type=text name=ADDRESS value='' />address on card<br/>
<input type=text name=CITY value='' /> city<br/>
<input type=text name=STATE value='' /> state<br/>
<input type=text name=ZIP value='' /> zip<br/>
<input type=text name=CARDNUM value='' /> cardnum<br/>
<input type=text name=EXPDATE value='' /> expdate<br/>
<input type=hidden name=xid value='$xid' />
<input type=hidden name=returl value='$self?done&xid=$xid' />
<input type=submit value='make payment' />
</form>
XXX;
echo $form;
?>