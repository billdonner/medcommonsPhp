<?php
include_once ('.config.inc.php'); 

require_once ('Amazon/FPS/Model/Refund.php');

/************************************************************************
 * Instantiate Implementation of Amazon FPS
 * 
 * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
 * are defined in the .config.inc.php located in the same 
 * directory as this sample
 ***********************************************************************/
 $service = new Amazon_FPS_Client(AWS_ACCESS_KEY_ID, 
                                       AWS_SECRET_ACCESS_KEY);

// get the price and tid

$price = $_POST['price'];

$paytid = $_POST['paytid'];

// @TODO: set request. Action can be passed as Amazon_FPS_Model_Refund 
 // object or array of parameters
 $time = time();
$request = new Amazon_FPS_Model_Refund();
$request->setTransactionId($paytid); //set the txn id
$request->setRefundTransactionReference("Refund $time");//Unique transaction reference  
$request->setTransactionDescription('chargeback');//description for the refund 
$amount = new Amazon_FPS_Model_Amount();
$amount->setCurrencyCode('USD');
$amount->setValue($price); //amount to be refunded
$request->setRefundAmount($amount);
//$request->setMarketplaceRefundPolicy(6);//This field is optional

invokeRefund($service, $request);
		
	 function invokeRefund(Amazon_FPS_Interface $service, $request) 
  {
      try {
              $response = $service->refund($request);
              
                echo ("Service Response\n");
                echo ("=============================================================================\n");

                echo("        RefundResponse\n");
                if ($response->isSetResponseMetadata()) { 
                    echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        echo("                RequestId\n");
                        echo("                    " . $responseMetadata->getRequestId() . "\n");
                    }
                } 
                if ($response->isSetRefundResult()) { 
                    echo("            RefundResult\n");
                    $refundResult = $response->getRefundResult();
                    if ($refundResult->isSetTransactionStatus()) 
                    {
                        echo("                TransactionStatus\n");
                        echo("                    " . $refundResult->getTransactionStatus() . "\n");
                    }
                    if ($refundResult->isSetTransactionId()) 
                    {
                        echo("                TransactionId\n");
                        echo("                    " . $refundResult->getTransactionId() . "\n");
                    }
                    if ($refundResult->isSetPendingReason()) 
                    {
                        echo("                PendingReason\n");
                        echo("                    " . $refundResult->getPendingReason() . "\n");
                    }
                } 

     } catch (Amazon_FPS_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
     }
 }
        	
header ("Location: /mod/voucherrefundback.php?paytid=$paytid");		
?>