<?php
require_once 'modpay.inc.php';
function sanitize($in)
{ if (isset($_REQUEST[$in])) return mysql_escape_string($_REQUEST[$in]);  else return 'notset'; }

$transactionId =sanitize('transactionId');
$referenceId = sanitize('referenceId');
$status = sanitize('status');
$operation = sanitize('operation');
$paymentReason = sanitize('paymentReason');
$transactionAmount= sanitize('transactionAmount');
$transactionDate = sanitize('transactionDate');
$paymentMethod= sanitize('paymentMethod');
$recipientName = sanitize('recipientName');
$buyerName = sanitize('buyerName');
$recipientEmail= sanitize('recipientEmail');
$buyerEmail = sanitize('buyerEmail');

$now = time();

$insert = <<<XXX
insert into fpsipn set
 transactionid = '$transactionId',
 referenceid =  '$referenceId',
 status =  '$status',
 operation =  '$operation',
 paymentReason =  '$paymentReason',
 transactionAmount=  '$transactionAmount',
 transactionDate =  '$transactionDate',
 paymentMethod=  '$paymentMethod',
 recipientName =  '$recipientName',
 buyerName =  '$buyerName',
 recipientEmail=  '$recipientEmail',
 buyerEmail =  '$buyerEmail',
 time = '$now';
XXX;
 
sql($insert) or die(mysql_error());

?>
 
