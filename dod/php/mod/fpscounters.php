<?php
/**
 * Handler that processes return from Amazon FPS and adjusts counters in line
 * with the embedded info in the transaction id.
 * <p>
 * Forwards to a URL provided in parameter 'next' after verifying 
 * and adjusting coutners.
 *
 * @author ssadedin@medcommons.net
 */
require_once 'modpay.inc.php';

$status = $_REQUEST['status'];
$next = $_REQUEST['next'];
if($status == 'PS') {
	$v = explode('-',$_REQUEST['referenceId']); 
	
	if (isset($_REQUEST['transactionId']))
	$tid = $_REQUEST['transactionId']; else $tid ='0';
	
  try {
    $status = wsAdjustCounters($v[0],$v[1],$v[2],$v[3]);
  }
  catch(Exception $e) {
    error_page("Unable to adjust billing counters for reference ".$_REQUEST['referenceId'],$e);
  }

  header("Location: $next");
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
	echo "<p><a href=personal.php>back home</a></p>";
}

