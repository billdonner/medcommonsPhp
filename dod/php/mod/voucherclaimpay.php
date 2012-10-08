<?php

require_once "modpay.inc.php";
require_once 'Crypt/HMAC.php'; #see http://pear.php.net/package/Crypt_HMAC
require_once 'HTTP/Request.php'; #see http://pear.php.net/package/HTTP_Request

$accessKey = "075Q8TW5Y9HFW4ZZAG02";
$secretKey = "IMBRcy/Lb/uqrOLF7GTWI7emGKt120o+BDWgzcIa";



$base = '0.04'; $rate = '8';  //what medcommons gets



function getMarketplaceWidgetForm($amount, $description, $referenceId, 
                             $immediateReturn, $returnUrl, $abandonUrl,
                             $processImmediate, $ipnUrl,
                             $recipientEmail, $fixedMarketplaceFee, $variableMarketplaceFee) {
    global $accessKey,$secretKey;
    $formHiddenInputs['accessKey'] = $accessKey;
    $formHiddenInputs['amount'] = $amount;
    $formHiddenInputs['description'] = $description;
    if ($referenceId) $formHiddenInputs['referenceId'] = $referenceId;        
    if ($immediateReturn) $formHiddenInputs['immediateReturn'] = $immediateReturn;    
    if ($returnUrl) $formHiddenInputs['returnUrl'] = $returnUrl;    
    if ($abandonUrl) $formHiddenInputs['abandonUrl'] = $abandonUrl;    
    if ($processImmediate) $formHiddenInputs['processImmediate'] = $processImmediate;   
    if ($ipnUrl) $formHiddenInputs['ipnUrl'] = $ipnUrl;   
    if ($recipientEmail) $formHiddenInputs['recipientEmail'] = $recipientEmail; 
    if ($fixedMarketplaceFee) $formHiddenInputs['fixedMarketplaceFee'] = $fixedMarketplaceFee; 
    if ($variableMarketplaceFee) $formHiddenInputs['variableMarketplaceFee'] = $variableMarketplaceFee; 
    
    ksort($formHiddenInputs);
    $stringToSign = "";
    
    foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
       $stringToSign = $stringToSign . $formHiddenInputName . $formHiddenInputValue;
    }

    $formHiddenInputs['signature'] = getSignature($stringToSign, $secretKey);

    $form = "<form action=\"https://authorize.payments.amazon.com/pba/paypipeline\" method=\"post\">\n";
    foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) { 
        $form = $form . "<input type=\"hidden\" name=\"$formHiddenInputName\" value=\"$formHiddenInputValue\" >\n";
    }
    $form = $form . "<input type=\"image\" src=\"https://authorize.payments.amazon.com/pba/images/payNowButton.png\" border=\"0\" >\n";
    $form = $form . "</form>\n";
    return $form;
}


// really start here

//list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

setlocale(LC_MONETARY, 'en_US');
$v->self = $_SERVER['REMOTE_ADDR'];

$time = time();
$v->xid = sha1($time.$v->self);
$v->err = $v->couponum = $v->price = $v->mcid = $v->servicename = $v->serviceemail = $v->supportphone =  $v->product = $v->paytype=  
 $v->expirationdate = $v->name=$v->address=$v->city=$v->state=$v->zip=$v->cardnum=$v->expdate='';


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
			$v->serviceemail = $r2->serviceemail;
			
			if ($r2->paytype!='')  // if already paid
			{
				header ("Location: voucherlist.php?i=$r2->svnnum&alreadypaid");
				die (" "); }

		}

//here
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];
$money = money_format('%i', $v->price);
$mony = monynf($v->price);

 $MarketplaceWidgetForm=getMarketplaceWidgetForm("$money", "MOD Patient Payment for $v->product",
	 'pnm'.'-'.$v->couponum.'-'.sha1($v->otp), // must be less than 100 chars
 			 "1", 
 			$GLOBALS['fps_voucherpurchase_done'] ,
                         	 $GLOBALS['fps_abandon'], "1", $GLOBALS['fps_ipn'],
                          	$v->serviceemail, "USD $base", "$rate");
                           
$header = page_header_nonav("page_pay","Pay $mony Online For Voucher" );
$footer = page_footer();

$markup = '<div id="ContentBoxInterior" mainTitle="Pay Online for Voucher">'.
"<h2>This voucher requires a payment of $mony</h2>".file_get_contents("payinpersonvoucher.html");
$markup = standardcoupon ($v->couponum,$markup);
$mid = <<<XXX
<h3>You can pay via Amazon</h3>
<p>
$MarketplaceWidgetForm
</p>
<h3>Or you can enter your credit card details</h3>
<p>
<form method='post' action='voucherpayccon.php'><input type='hidden' name='c' value="$v->couponum" />
<input disabled='disabled' type='image' alt="All 4 Credit Card Web Logos" src="http://www.instamerchant.com/cards4.gif" width="239" height="40" border='0' />
</form>
</p>
<table class='tinst'>
<tbody>
<tr><td class='lcol'>Instructions</td><td class='rcol'>Pay directly from your Amazon Account<br />You will be prompted to create an Amazon account 
if you don't already have one.<br />Paying via Credit Card does not leave any trail in MedCommons</td></tr>
</tbody>
</table>
</div>
XXX;
echo $header.$markup.$mid. $footer;

?>
