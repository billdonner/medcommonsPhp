
<?php

require_once "modpay.inc.php";

list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();
//list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();
$v = new stdClass;
$v->err = $v-> accesscode_err = $v->secretkey_err = $v->secretkey = $v->accesscode =$v->couponum = $v->otp =  '';

// error check these args
$errs = array (); $errstring ='';

if (isset($_GET['c']))
{
	$v->couponum = $_GET['c'];

} else die ('No coupon number specified');



$now=time();

$result =sql("Select * from  modcoupons c, modservices s  where  c.couponum = '$v->couponum' and c.svcnum=s.svcnum ")
or die ("Cant query modcoupons ".mysql_error());
$r2= mysql_fetch_object($result);
if ($r2===false)
die('No vouchers match that voucher ID or One Time Password');

//
// if it has been revoked, then say so

if ($r2->status=='revoked') {header("Location: voucherclaim.php"); die("Revoked voucher");} 

//
// check to make sure it is paid

if (!isset($_GET['p'])) sql("Update modcoupons set status='accessed' where couponum='$v->couponum'  ");
//registerpost.php - process MOD register request


    $source = "copyvoucher.html";$body=file_get_contents($source);
    $header = page_header_nonav("page_copy","Copy Voucher  - MedCommons on Demand" );
    $roo = <<<XXX
<table class=tinst><tbody>
<tr><td class=lcol >Instructions</td><td class=rcol > 
   Click 
<a target='_new' title='open records in new window' 
href='$r2->hurl' onclick='return open_hurl($r2->couponum)'>
<img border=0 src='/images/icon_healthURL.gif' alt=hurlimg />Here</a> to Open and Preview.  You can also <a href="**CHURL**">Copy to a Permanent HealthURL</a>.</td></tr>
</tbody>
</table>
XXX;

$couponnum = $_REQUEST['c'];

$footer = page_footer();
$markup = '<div id="ContentBoxInterior" mainTitle="Copy MedCommons Voucher" >'."
<script type='text/javascript'>
  function open_hurl(cnum) {
    window.open('hurlredir.php?cnum='+cnum+'&c=v');
    return false;
  }
  </script>
  $body</div>";



$markup = standardcoupon ($couponnum,$markup.$roo);


echo $header.$markup.$footer;
?>
