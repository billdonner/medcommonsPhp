<?php

require_once "modpay.inc.php";

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

$vprice = '$'.money_format('%i', $price/100.0);

$nowtime = time();
$action = "ccpay.php";
$blurb = "<p>$ptime: In a separate window we are initiating the purchase of <br/>
Product $product<br/>
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
    <title>MedCommons Payflow Payment V 2 price $price vprice $vprice</title>
     <meta name="robots" content="none">
    <script language="javascript">
      function init() {
      foo.submit();
      }
    </script>
  </head>
<BODY onload="init()">
<img src='/images/smallwhitelogo.gif'>
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

</p>
</body>
</html>
XXX;
echo $x;
exit;

}

// really start here

setlocale(LC_MONETARY, 'en_US');
$v->self = $_SERVER['REMOTE_ADDR'];

$time = time();
$v->xid = sha1($time.$v->self);
if (isset($_REQUEST['xid']))
{
	repost();
	exit;
}

$v->err = $v->couponum = $v->price = $v->mcid = $v->servicename = $v->supportphone  =$v->expirationdate = $v->paytype =$v->product =  '';
$v->name=$v->address=$v->city=$v->state=$v->zip=$v->cardnum=$v->paptientprice = $v->expdate='';
$header =page_header('payviacc' ,"Pay For a MedCommons Voucher With Credit Card");
$footer = page_footer();

// error check these args
$errs = array (); $errstring ='';
$coupon = $_POST['c'];
$result =sql("Select * from  modcoupons c ,modservices s  where  c.couponum='$coupon' and c.svcnum=s.svcnum " )
		or die ("Cant query modcoupons ".mysql_error());
		$r2= mysql_fetch_object($result);
		if ($r2===false)
		$errs[] = array('err','No vouchers match that voucher number');

		if (count($errs)==0) {
			$v->couponum = $r2->couponum;
			$v->price = $r2->patientprice/100.;
			$v->patientprice = $r2->patientprice;
			$v->name = $r2->patientname;
			$v->mcid = $r2->mcid;
			$v->accid = $r2->accid;
			$v->servicename = $r2->servicename;
			$v->product = $r2->servicedescription.' '.$r2->addinfo;
			$v->supportphone = $r2->supportphone;
			$v->otp = $r2->otp;
		}

//here
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];

$ret =$GLOBALS['cc_purchase_done'] ;


$form = <<<XXX
<h5>This will put a real payment thru the MedCommons Payments system</h5>

<form method=post action='voucherpaycc.php' >
<input type=hidden name=partner value='$v->accid' />
<input type=hidden name=accid value='$v->mcid' />
<input type=hidden name=product value='$v->servicename' />
<input type=hidden name=price  value='$v->patientprice'  />
<input type=hidden name=xid value='$v->xid' /> 
<input type=hidden name=returl value='$ret' />
<div class=field><span class=n>Name</span><span class=q><input type=text name=NAME value='$v->name' /><span class=r>as it appears on the card</span>
<div class=inperr id=name_err>&nbsp;</div></span></div>
<div class=field><span class=n>Address</span><span class=q><input type=text name=ADDRESS value='$v->address' /><span class=r>&nbsp;</span>
<div class=inperr id=address_err>&nbsp;</div></span></div>
<div class=field><span class=n>City</span><span class=q><input type=text name=CITY value='$v->city' /><span class=r>&nbsp;</span>
<div class=inperr id=CITY_err>&nbsp;</div></span></div>
<div class=field><span class=n>State</span><span class=q><input type=text name=STATE  value='$v->state' /><span class=r>&nbsp;</span>
<div class=inperr id=state_err>&nbsp;</div></span></div>
<div class=field><span class=n>Zip</span><span class=q><input type=text name=ZIP value='$v->zip' /><span class=r>&nbsp;</span>
<div class=inperr id=zip_err>&nbsp;</div></span></div>
<br/>
<div class=field><span class=n>Card Number</span><span class=q><input type=text name=CARDNUM value='$v->cardnum' /><span class=r>no spaces</span>
<div class=inperr id=cardnum_err>&nbsp;</div></span></div>
<div class=field><span class=n>Expiration Date</span><span class=q><input type=text name=EXPDATE value='$v->expdate' /><span class=r>mm/yy</span>
<div class=inperr id=expdate_err>&nbsp;</div></span></div>
<div class=field><span class=n>&nbsp;&nbsp;</span><span class=q><input type=submit class=primebutton
value='Pay via Credit Card' /></span></div>
<br/>
</form>
XXX;


$mony = monynf($v->price);

$header = page_header("page_payinpersoncc","Pay $mony In Person By Credit Card  - MedCommons on Demand" );
$footer = page_footer();
$markup = '<div id="ContentBoxInterior" mainTitle="Pay In Person By Credit Card MedCommons Voucher" >'.
"<h2>Accept payment of $mony by credit card in-person or via phone</h2>".file_get_contents("payinpersonvoucher.html");
$markup = standardcoupon ($v->couponum,$markup);

$mid = <<<XXX
<p>
<img border=0 alt="All 4 Credit Card Web Logos" src="http://www.instamerchant.com/cards4.gif" width="239" height="40" border=0 />
</p>
<div class=fform>
$form
<p><script src="https://seal.verisign.com/getseal?host_name=payments.verisign.com&size=M&use_flash=NO&use_transparent=NO"></script></p>
</div>
<table class=tinst >
<tbody >
<tr ><td class=lcol >Instructions</td><td class=rcol >Ask the patient for credit card information.
<br/>The patient's card is not kept on file and will need to be re-entered for every transaction.
<br/>The patient can also pay online via credit card or an Amazon Payments account.</td></tr>
</tbody>
</table>
</div>
XXX;

echo $header.$markup.$mid. $footer;
?>
