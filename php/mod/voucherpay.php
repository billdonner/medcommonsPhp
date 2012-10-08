<?php

require_once "modpay.inc.php";

require_once 'Crypt/HMAC.php'; #see http://pear.php.net/package/Crypt_HMAC
require_once 'HTTP/Request.php'; #see http://pear.php.net/package/HTTP_Request

$accessKey = "075Q8TW5Y9HFW4ZZAG02";
$secretKey = "IMBRcy/Lb/uqrOLF7GTWI7emGKt120o+BDWgzcIa";

// really start here


list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

setlocale(LC_MONETARY, 'en_US');
$v->self = $_SERVER['REMOTE_ADDR'];

$time = time();
$v->xid = sha1($time.$v->self);
$v->err = $v->couponum = $v->price = $v->mcid = $v->servicename = $v->supportphone =  $v->product = $v->paytype=  
 $v->expirationdate = $v->name=$v->address=$v->city=$v->state=$v->zip=$v->cardnum=$v->expdate='';
$header = page_header("page_pay","Pay in Person For a MedCommons Voucher" );
$footer = page_footer();

// error check these args
$errs = array (); $errstring ='';
$coupon = $_REQUEST['c'];
$result =sql("Select * from  modcoupons c ,modservices s  where  c.couponum='$coupon' and c.svcnum=s.svcnum " )
		or die ("Cant query modcoupons ".mysql_error());
		$r2= mysql_fetch_object($result);
		if ($r2===false)
		$errs[] = array('err','No vouchers match that voucher number');
		if (count($errs)==0) {
			$v->couponum = $r2->couponum;
			$v->price = $r2->patientprice/100.;
			$v->name = $r2->patientname;
			$v->product = $r2->servicedescription.' '.$r2->addinfo;
			$v->mcid = $r2->mcid;
			$v->accid = $r2->accid;
			$v->servicename = $r2->servicename;
			$v->supportphone = $r2->supportphone;
			$v->otp = $r2->otp;
			$v->paytype = $r2->paytype;
			$v->expirationdate = $r2->expirationdate;
		}

//here
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];


$ret =$GLOBALS['fps_purchase_done'] ;
$otp = sha1 ($v->otp);
$mony = monynf($v->price);

$header = page_header("page_payinperson","Pay $mony In Person  - MedCommons on Demand" );
$footer = page_footer();
$markup = '<div id="ContentBoxInterior" mainTitle="Pay In MedCommons Voucher" >'.
"<h2>This voucher requires a payment of $mony</h2>".file_get_contents("payinpersonvoucher.html");
$markup = standardcoupon ($v->couponum,$markup);
$mid = <<<XXX
<h3>You can accept cash in person or over the phone</h3>
<p>
<span id=outerbuttons>
<form method='post' style='display: inline;' action=voucherpaidcash.php ><input type=hidden name=c value=$v->couponum />
<input type=hidden name=o value=$otp />
<input type=hidden name=p value=p />
<input type=submit value='Paid In Full' class=primebutton name='dopay' />
</form>&nbsp;
<form method=post action=voucherclaim.php style='display: inline;'><input type=hidden name=c value=$v->couponum />
<input type=submit class='altshort' value=Cancel />
</form>
</span>
</p>
<h3>Or you can put in the patient's credit card details (disabled)</h3>
<p>
<form method=post  ><input type=hidden name=c value=$v->couponum />
<input disabled type=image alt="All 4 Credit Card Web Logos" src="http://www.instamerchant.com/cards4.gif" width="239" height="40" border=0 />
</form>
</p>
<table class=tinst >
<tbody >
<tr ><td class=lcol >Instructions</td><td class=rcol >Try to collect payment in-person via cash or credit card.<br/>Alternatively, your patient can pay online using the instructions on the printed voucher.<br/>Access to results will not be available until payment is made.</td></tr>

</tbody>
</table>
</div>
XXX;
echo $header.$markup.$mid. $footer;

?>
