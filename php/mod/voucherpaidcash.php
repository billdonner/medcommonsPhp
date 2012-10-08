<?php
require_once 'modpay.inc.php';
	$v1 = $_REQUEST['c'];
	$otp = $_REQUEST['o'];
	$result = sql("Select * from modcoupons where couponum='$v1' ") ;
	$r2=mysql_fetch_object($result);
	if ($otp == sha1($r2->otp))
	{
	
	$tid=sha1(time());
	$status = sql("Update modcoupons set paytid='$tid',paytype='cash' where couponum='$v1'  ");
	if (!$status ) die ("Cant update modcoupons in voucherpaidcash ".mysql_error());
	
	// should check v2 against otp here
	//header("Location: voucherhome.php?p=p&c=$v1&o=$otp");
	
	
	// bump the counters in the services record
			$result = sql ("Select * from modservices where svcnum = '$r2->svcnum' ");
			if (!$result) die ("cant select modservices " . mysql_error());

			if ($result) {
				$r3 = mysql_fetch_object ($result);
				if (!$r3) die ("cant fetch modservices " . mysql_error());
				list  ($netpractice, $netmc, $amazonfee) = figure_money($r2->patientprice,$r2->duration,$r2->asize,$r2->fcredits,$r2->dcredits);

				$ip1 = $r3->utilizedcount+1;
				$cash = $r3->cashreceived+$r2->patientprice;
				$paid = $r3->cashpaidout+ ($netmc+$amazonfee);
				$result = sql ("Update modservices set cashreceived = '$cash', 
								cashpaidout = '$paid', utilizedcount = '$ip1' where svcnum = '$r2->svcnum' ");
				if (!$result) die ("cant update modservices " . mysql_error());
			}

	header("Location: voucherlist.php?i=$r2->svcnum");
	
	}
	die ("Cant find coupon ");
?>
