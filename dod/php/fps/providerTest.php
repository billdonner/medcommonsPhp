<?
#       Copyright 2007 Amazon Technologies, Inc.  Licensed under the Apache License, Version 2.0 (the "License");
#       you may not use this file except in compliance with the License. You may obtain a copy of the License at:
#
#       http://aws.amazon.com/apache2.0
#
#       This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#       See the License for the specific language governing permissions and limitations under the License.
#

#To use this sample, replace the values of $accessKey and $secretKey with the values for your account

require_once 'Crypt/HMAC.php'; #see http://pear.php.net/package/Crypt_HMAC
require_once 'HTTP/Request.php'; #see http://pear.php.net/package/HTTP_Request

# 	Please us PHP 5.1.1 or later to run this sample
#	1. Replace the variables $accessKey and $secreyKey with your Access Key Id and Secret Access Key
#	2. Save this file as a .php file in the htdocs folder of your Apache installation, and open it in a browser
#	3. Alternatively, you can run this sample code as a PHP application
#	4. The code sample will output a test Marketplace Widget form and a Accept Marketplace Fee form
#	5. You can change the parameters in the HTML Body below to get your own HTML Form
#       6. This sample, by default, generates a form which points to the Amazon Payments Sandbox.
#          To use this widget in production:
#          Replace "authorize.payments-sandbox.amazon.com" with "authorize.payments.amazon.com" in the generated HTML
#



$accessKey = "075Q8TW5Y9HFW4ZZAG02";
$secretKey = "IMBRcy/Lb/uqrOLF7GTWI7emGKt120o+BDWgzcIa";


function getAcceptMarketplaceFeeForm($referenceId, $returnUrl, $fixedMarketplaceFee, $variableMarketplaceFee) {
  global $accessKey,$secretKey;
    $formHiddenInputs['callerKey'] = $accessKey;
    $formHiddenInputs['pipelineName'] = "Recipient";
    $formHiddenInputs['recipientPaysFee'] = "True";
    $formHiddenInputs['collectEmailAddress'] = "True";
    if ($referenceId) $formHiddenInputs['callerReference'] = $referenceId;
    if ($returnUrl) $formHiddenInputs['returnUrl'] = $returnUrl;
    if ($fixedMarketplaceFee) $formHiddenInputs['maxFixedFee'] = $fixedMarketplaceFee;
    if ($variableMarketplaceFee) $formHiddenInputs['maxVariableFee'] = $variableMarketplaceFee;

    ksort($formHiddenInputs);
    $stringToSign = "";

    foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
       $stringToSign = $stringToSign . $formHiddenInputName . $formHiddenInputValue;
    }

    $formHiddenInputs['signature'] = getSignature($stringToSign, $secretKey);

    $form = "<form action=\"https://authorize.payments.amazon.com/cobranded-ui/actions/start\" method=\"post\">\n";
    foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
        $form = $form . "<input type=\"hidden\" name=\"$formHiddenInputName\" value=\"$formHiddenInputValue\" >\n";
    }
    $form = $form . "<input type=\"image\" src=\"https://authorize.payments.amazon.com/pba/images/MarketPlaceFeeWithLogo.png\" border=\"0\" >\n";
    $form = $form . "</form>\n";
    return $form;
}




function getSignature($stringToSign) {
   global $accessKey,$secretKey;
    $hmac = new Crypt_HMAC($secretKey,"sha1");
    $binary_hmac = pack("H40", $hmac->hash(trim($stringToSign)));
    return base64_encode($binary_hmac);
}

$price = "2.00"; $base = '0.04'; $rate = '8';


                           
                           
$AcceptMarketplaceFeeForm= getAcceptMarketplaceFeeForm("MyReference-010105", "http://tenth.medcommons.net/fps/accreturn.php", "$base", "$rate") ;

$msg = <<<XXX
<html>     
<body><h2>A USD $price  payment from Patient to Provider billdonner@gmail.com, MedCommons gets $base plus $rate percent </h2>
<p>This is where the provider billdonner@gmail.com agrees to medcommons cut and pricing in general</p>$AcceptMarketplaceFeeForm
    
<p><a href='index.php' >main test</a></p>

</body>
</html>
XXX;
echo $msg;
?>
