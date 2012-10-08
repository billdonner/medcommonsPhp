<?php
require_once 'modpay.inc.php';
$status = $_REQUEST['status'];
if ($status == 'PS')
{
	// if here its because we have somehow paid
	$vv = explode('-',$_REQUEST['referenceId']); 
	
	if (isset($_REQUEST['transactionId']))
	$tid = $_REQUEST['transactionId']; else $tid ='0';
	$v->couponum = $v1= $vv[1];
	$v->otp = $v2 = $vv[2];
	 $status = updatepaidstatus ($v1,$v2,$tid,'amzfps');
	 if (!$status) die ("Can not update paid status in voucherpaidfps ".mysql_error());
	header("Location: voucherhome.php?p=p&c=$v1&o=$v2");
	
}
else
{
	$header = <<<XXX
<html><head><title>MedCommons FPS Pay for Voucher Services</title>
     <link media='all'
	href='/css/medCommonsStyles.css'  type='text/css' rel='stylesheet' /></head>
    <body><img border='0' src='/images/HP_logo.jpg' />
    <h1>MedCommons FPS Product Catalog</h1>
    <div style='float:left; margin:10px;' >
XXX;

	echo $header.
	'<h2>Unusual Return from Amazon FPS Pipeline</h2>';
	echo '<p>status: '.$_REQUEST['status'].'</p>';
	if (isset($_REQUEST['referenceId']))
	echo '<p>referenceId: '.$_REQUEST['referenceId'].'</p>';
	if (isset($_REQUEST['transactionId']))
	echo '<p>transactionId: '.$_REQUEST['transactionId'].'</p>';
	echo "<p><a href=/index.php >back home</a></p>";
}
echo "
<img src=https://images-na.ssl-images-amazon.com/images/G/01/webservices/AWS_LOGO._V2289989_.gif  border='0' />
</body>
</html>";

?>
