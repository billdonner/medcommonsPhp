<?php

//registerpost.php - process MOD register request
require_once "modpay.inc.php";
list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

$couponnum = $_REQUEST['c'];

$header = page_header("page_preview","Voucher Preview - MedCommons on Demand" );
$footer = page_footer();
$markup = '<div id="ContentBoxInterior" mainTitle="Preview MedCommons Voucher" >'.file_get_contents("displaycoupon.html");
$markup = standardcoupon ($couponnum,$markup);

echo $header.$markup.$footer;

?>
