<?php

function sanitize($in)
{ if (isset($_REQUEST[$in])) return ($_REQUEST[$in]);  else return '(not set)' ; }




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


echo "<h2>FPS says: $status</h2>";
echo "<p><a href='index.php' >main test</a></p>";
echo "Error: $error<br/>";
echo "Transaction id: $transactionId <br/>";
echo "Reference id: $referenceId <br/>";
echo "Operation: $operation <br/>";
echo "Payment Reason: $paymentReason <br/>";
echo "Transaction Amount: $transactionAmount <br/>";
echo "Transaction Date: $transactionDate <br/>";
echo "Payment Method: $paymentMethod <br/>";
echo "Recipient Name: $recipientName <br/>";
echo "Buyer Name: $buyerName <br/>";
echo "Recipient Email: $recipientEmail <br/>";
echo "Buyer Email: $buyerEmail <br/>";
?>

