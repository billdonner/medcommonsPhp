<?php 
require_once "modpay.inc.php";

// finds all vouchers where expiration date = N days from now (N may == 0)
// for each voucher do case based on action:
//
// expired ==    set voucher to expired, call a web service in Simon world to announce this and get rid of healthURL and amazon
//
// firstwarn == send first warning email to the user about expiration in N days
//
// lastwarn == send stronger second warning email to the user about imminent destruction of the voucher
// this should run in  the background as a cron job

$VOUCHERS =$EXPIRED=$FIRSTWARN=$LASTWARN=0;

function expired($r)
{	
global $EXPIRED;
$EXPIRED++;
$time = time();
$result = sql("update modcoupons set timeofexpiry='$time' where couponum='$r->couponum' ");
if (!$result) die ("Cant update modcoupons " .mysql_error());
return "done";
}
function genericwarn($r,$custom)
{
	// if we have an email address for the user, lets send an email
	if ($r->patientemail!='')
	{

		if ($GLOBALS['voucherid_solo']) $claimUrl = 'https://'.$_SERVER['HTTP_HOST'].'/pickuprecords.php'; else
		$claimUrl =$GLOBALS['voucher_pickupurl'] ; // $GLOBALS['mod_base_url']."/voucherclaim.php";
		$claimUrl .="?voucherid=$r->voucherid";
		$voucherexpirationdate=$r->expirationdate;

		$voucherpaystatus=paidvia($r->patientprice,$r->paytype);

		$voucherprice='0.00';
		$payin="There is no charge for this service.";
		if ($r->servicelogo!='')
		$servicelogo = "<img src='$r->servicelogo' border=0 />"; else $servicelogo = '';

		if ($r->patientprice!=0)
		{
			$payin="These records have already been paid via $r->paytype";
			$voucherprice='$'.money_format('%i', $r->patientprice/100.);
			if ($r->paytype=='' )$payin = " There is a balance due of $voucherprice.";
		}
		$msg = <<<XXX
<b>Dear $r->patientname,</b>\n	

<p>$r->servicename  wants you to know your MedCommons voucher $r->voucherid is set to expire on $r->expirationdate. You will need to print or copy your records into a permanent MedCommons Account to retain them beyond that date.</p>\n			
<p>$custom</p>
<p>You can pick up your records at $claimUrl</p>\n
<p>Details of the service performed by $r->servicename:  $r->servicedescription $r->addinfo</p>
<p>$payin</p>
<p>Be sure to have your voucher password available to access these records.  If you have lost your password please contact your healthcare provider at $r->supportphone.</p>
<p>Thank you,</p>
<p>$r->servicename</p>
<p>$servicelogo</p>
<p><small>This mail produced by  <a href='https://www.medcommons.net/'>MedCommons on Demand</a></small></p>\n
XXX;
		$wrapped = <<<XXX
<html>
<head>
  <title>Voucher $r->voucherid from $r->servicename </title>
</head>
<body>
$msg
</body>
</html>
XXX;
		// To send HTML mail, the Content-type header must be set Bcc: billdonner@gmail.com
		$headers  = <<<XXX
MIME-Version: 1.0
User-Agent: MedCommons Mailer 1.0
Content-type: text/html; charset=iso-8859-1
To: $r->patientemail
From: $r->servicename <$r->serviceemail>
Reply-To:$r->serviceemail
XXX;
		$status = mail($r->patientemail,"$r->servicename says your voucher $r->voucherid is about to expire",$wrapped,$headers);
		return true;
	}
	return false;
}

function lastwarn($r)
{	global $LASTWARN;
if (genericwarn($r,""))  $LASTWARN++;
return "done";
}
function firstwarn($r)
{	global $FIRSTWARN;
if (genericwarn($r,"")) $FIRSTWARN++;
return "done";
}

$ac = $_SERVER['argc'];$av = $_SERVER['argv'];
if ($ac<2) die ("usage >batch.php    N     ACTION");
$ndays = $av[1];
$action= $av[2];
$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_User']= "medcommons";
$GLOBALS['DB_Password'] ='';
$GLOBALS['DB_Database']='mcx';
mysql_pconnect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
mysql_select_db($GLOBALS['DB_Database']) or die ("can not connect to database $db");
$starttime = time();
$t = gmdate('M d Y H:i:s');
$self = $_SERVER['PHP_SELF']; // these are not available when ryb on command line???

echo "\n\rgmt $t > $self is selecting vouchers expiring in $ndays days and performing the action $action\n\r";
$stmt = "SELECT * from modcoupons c, modservices s WHERE (CURDATE() + INTERVAL $ndays DAY) = c.expirationdate and c.svcnum = s.svcnum "; // check each coupon that is expiring soon
$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());
while ($r = mysql_fetch_object($result))
{
	$VOUCHERS++;
	switch ($action )
	{
		case 'expired': { $ret = expired($r); break;}

		case 'firstwarn': { $ret = firstwarn($r); break;}

		case 'lastwarn': { $ret = lastwarn($r); break;}

		default : { $ret = 'bad case';break;}
	}
	//echo "$r->voucherid $action issued to $r->patientname status $r->status $r->paytype $ret \r\n";
}
mysql_free_result($result);
$t = 'gmt '.gmdate('M d Y H:i:s');
echo "$t > vouchers processed: $VOUCHERS  expiring: $EXPIRED   first warning emails: $FIRSTWARN   last warning emails: $LASTWARN\n\r";
exit;
?>