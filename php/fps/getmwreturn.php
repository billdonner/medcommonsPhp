<?php

function fps_error_page()
{
$markup = '';

function sanitize($in) { if (isset($_REQUEST[$in])) return ($_REQUEST[$in]);  else return '(not set)' ; }


$error =sanitize('errorMessage');
$status = sanitize('status');
$signature = sanitize('Signature');
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


$markup .=  "<h2>FPS Marketplace says: $status</h2>";
$markup .=  "<p><a href='index.php' >main test</a></p>";
$markup .=  "Error: $error<br/>";
$markup .=  "Transaction id: $transactionId <br/>";
$markup .=  "Reference id: $referenceId <br/>";
$markup .=  "Operation: $operation <br/>";
$markup .=  "Payment Reason: $paymentReason <br/>";
$markup .=  "Transaction Amount: $transactionAmount <br/>";
$markup .=  "Transaction Date: $transactionDate <br/>";
$markup .=  "Payment Method: $paymentMethod <br/>";
$markup .=  "Recipient Name: $recipientName <br/>";
$markup .=  "Buyer Name: $buyerName <br/>";
$markup .=  "Recipient Email: $recipientEmail <br/>";
$markup .=  "Buyer Email: $buyerEmail <br/>";

return $markup;
}
echo fps_error_page();
?>
