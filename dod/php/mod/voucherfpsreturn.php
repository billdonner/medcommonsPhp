<?php
require_once 'modpay.inc.php';


$status = $_REQUEST['status'];
if ($status == 'PS')
{
	$v = explode('-',$_REQUEST['referenceId']); 
	
	if (isset($_REQUEST['transactionId']))
	$tid = $_REQUEST['transactionId']; else $tid ='0';
	
	$status = wsAdjustCounters($v[0],$v[1],$v[2],$v[3]); 
	$st = $status?1:0;
	
	sql("Update modcoupons set paytid='$tid',paytype='amazon' where couponum='{$v[0]}'  ");
	// should check v2 against otp here
	header("Location: ".$GLOBALS['fps_purchase_done']);//."?st=$st&v0=".$v[0].'&v1='.$v[1].'&v2='.$v[2].'&t='.$tid);
	
}

else


echo fps_error_page();

?>
