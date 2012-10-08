<?php

//registerpost.php - process MOD register request
require_once "modpay.inc.php";
require_once "utils.inc.php";
require_once "JSON.php";

list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

$couponnum = $_REQUEST['c'];
$now=time();
$result = sql ("Select c.*, s.servicedescription, s.supportphone, s.servicename, s.servicedescription, s.accid, s.servicelogo, s.voucherprinthtml from modcoupons  c, modservices s where c.couponum='$couponnum'  and c.svcnum=s.svcnum ");
$r = mysql_fetch_object($result);
$timenow = strftime ('%T %D', $now);
// Calculate the bar code url
$paidvia = paidvia($r->patientprice,$r->paytype);
// add some fields for show_coupon
$r->price = $r->patientprice/100.;
$r->name = $r->patientname;
$r->product = $r->servicedescription.' '.$r->addinfo;
$payin = 'You will be asked to pay for the service.<br/>';
$header = <<<XXX
<h2>Printed Voucher for $r->servicename</h2>
XXX;
dbg("account $r->couponum has fax credits ".$r->fcredits);
if ($r->fcredits>0) {
// get a fax cover id
$cover_result_json = get_url($GLOBALS['appliance_accts']."/ws/createFaxCover.php?accid=".$r->mcid);
dbg("got json ".$cover_result_json);
$json = new Services_JSON();
$cover_result = $json->decode($cover_result_json);
if(!$cover_result)
  die("Failed to register fax cover sheet.");
$coverId = $cover_result->result->cover_id;
dbg("got cover id ".$coverId);

$barcode=encode_fax($accid, $coverId);
$barImgUrl="https://secureservices.dataoncall.com/CreateBarCode.serv?BARCODE=$barcode&CODE_TYPE=DATAMATRIX&DM_DOT_PIXELS=8";
$faxcover ="<div id=faxcovercode><img src=$barImgUrl alt='fax barcode'  border=0 /></div>";
}
else $faxcover='';
$providerphone=$r->supportphone;
$servicename =$r->servicename;
$servicedescription=$r->servicedescription;
$addinfo = $r->addinfo;
$serviceproviderid=$r->accid;
$patientname=$r->patientname;
$patienttempid=$r->voucherid;
$voucherexpirationdate=$r->expirationdate;
$voucherpaystatus=paidvia($r->patientprice,$r->paytype);

if ($r->patientprice==0) { $voucherprice='0.00'; $payin='';} else
$voucherprice='$'.money_format('%i', $r->patientprice/100.);
$servicelogo = "<img src='$r->servicelogo' border=0 />";
$thurl = $r->mcid;
$issuetime = date("M d Y H:i:s",  $r->issuetime);
if ($GLOBALS['voucherid_solo']) $claimUrl = 'https://'.$_SERVER['HTTP_HOST'].'/pickuprecords.php'; else
$claimUrl =$GLOBALS['voucher_pickupurl'] ; // $GLOBALS['mod_base_url']."/voucherclaim.php";
$time = date("M d Y H:i:s",  $r->issuetime);
$voucherid = $r->voucherid;
$header = $r->servicename;
$passw = $r->otp;

$tophead = <<<XXX
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- 
MedCommons Voucher for Provider Services

$r->servicedescription

  Copyright MedCommons 2008
 -->
<html>
<head>
    <title>MedCommons Voucher for  $servicename</title>
    <link rel="stylesheet" type="text/css" href="/css/cover.css"/>
</head>
<body body onload="window.focus(); window.print();">
XXX;

if ($r->voucherprinthtml!='') $markup = $r->voucherprinthtml;  
else 
if ($r->fcredits>0) $markup = file_get_contents("printedcoupon.html"); else $markup = file_get_contents("printedcouponnofax.html");

// swap all of the sections

$markup = str_replace( array('**AI**','**TI**','**VI**','**PW**','**CU**','**SL**','**HD**','**FC**',
'**SPH**','**SN**','**SD**','**SPID**','**SPN**','**STID**','**SXD**','**SPR**','**SPS**','**IT**','**THURL**','**PAYIN**')
,

array($addinfo, $time, $voucherid,$passw,$claimUrl,$servicelogo,$header,$faxcover
,$providerphone,$servicename,$servicedescription,$serviceproviderid,$patientname,$patienttempid,$voucherexpirationdate,$voucherprice,
$voucherpaystatus,$issuetime,$thurl,$payin)
,
$markup);

echo $tophead.$markup."</body></html>";

?>
