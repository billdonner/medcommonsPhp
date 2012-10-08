<?php
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
#	1. Insert your access key and secret key in the appropriate places mentioned below
#	2. Save this file as 'createPayNowWidget.php'
#	3. Open this file in a browser.
#	4. You'll see a test Pay Now Widget form
#	5. You can change the parameters in the HTML Body below to get your own HTML Form
#       6. This sample, by default, generates a form which points to the Pay Now Widget Sandbox.
#          In order to start pointing to Pay Now Widget Production, do the following:
#          Replace "authorize.payments-sandbox.amazon.com" with "authorize.payments.amazon.com" in the generated HTML
#

$accessKey = "075Q8TW5Y9HFW4ZZAG02";
$secretKey = "IMBRcy/Lb/uqrOLF7GTWI7emGKt120o+BDWgzcIa";

require_once "modpay.inc.php";

$mess ='';

list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

$masteraccid = get_master_services_accid($accid);

// if that didnt work, use a default account for now
if ($btk=='') $btk ='00000000000000000000';


list ($faxin,$dicom,$acc) =wsGetCounters($btk);
$all_counters = <<<XXX
    <p>
    <table>
    <tr><td>FAXIN COUNTER</td><td>$faxin</td></tr>
      <tr><td>DICOM COUNTER</td><td>$dicom </td></tr>
      </table>
    </p>
XXX;
	
$header = page_header_nonav("page_catalog","MedCommons Product Catalog" );
$footer = page_footer();
$buttons = 
AmzPayNowButton("USD .50", array(0,100,0),"DICOM10", "10 Dicom Uploads", $btk).
AmzPayNowButton("USD .10",array(200,0,0),"FAXIN20", "20 Incoming Fax Pages", $btk);


echo <<<XXX
$header
<div id="ContentBoxInterior"   mainId='page_catalog' mainTitle="MedCommons Product Catalog">
<h2><b>Buy more here</b></h2>
<p>
$all_counters
</p>  
<div style='float:left; margin:10px' ><h3>MedCommons Products For Health Services and Imaging Centers</h3>
    $buttons
      <p>MedCommons uses Amazon S3 services for payment processing and  long term records storage.     <img src='http://developers.facebook.com/images/aws.gif' /></p>
<ul>
<li><b>View</b> the Amazon <a href='http://www.amazon.com/dp-applications' >Application Billing Page</a></li>
<li><b>Inquire</b> about your bill at <a href=''mailto:application-payments@amazon.com' >application-payments@amazon.com</a></li>
</ul>
You need to factor in the cost of these credits when pricing your Service.  You 
will receive your "Service Price" as printed on the Voucher coupon given to 
your patient, lessless the actual DICOM studies and Fax pages used by the 
patient. If your credit balance goes to 0, you will need to replenish the DICOM 
and/or  FAX credits in order to accommodate your patients.
</p>
</div>
</div>
$footer
XXX;

?>
