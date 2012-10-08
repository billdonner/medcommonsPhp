<?
/***************************************************************
 *
 * Functions to support rendering buttons for payment via Amazon
 *
 * $Id$
 */
@require_once 'Crypt/HMAC.php'; #see http://pear.php.net/package/Crypt_HMAC
@require_once 'HTTP/Request.php'; #see http://pear.php.net/package/HTTP_Request

require_once 'settings.php';
require_once 'utils.inc.php';

/**
 * Generate a signature for the given string using our secret amazon key
 */
function getSignature($stringToSign) {
	global $acAmazonFPSSecret;
	$hmac = new Crypt_HMAC($acAmazonFPSSecret,"sha1");
	$binary_hmac = pack("H40", $hmac->hash(trim($stringToSign)));
	return base64_encode($binary_hmac);
}

/**
 * Generate a button with a form including signature, ready to send user to amazon
 */
// AmzPayNowButton("USD .50", "DICOM10", "10 Dicom Uploads", $btk).
function AmzPayNowButtonForm($amount, $short, $description, $referenceId, $returnUrl=false, $abandonUrl=false) {
	global $acAmazonFPSKey,$acAmazonFPSSecret,$acAmazonPayUrl;
	$formHiddenInputs['accessKey'] = $acAmazonFPSKey;
	$formHiddenInputs['amount'] = $amount;
	$formHiddenInputs['description'] = $description;
	if ($referenceId) $formHiddenInputs['referenceId'] = $referenceId;
	$formHiddenInputs['immediateReturn'] ='1';
	$formHiddenInputs['processImmediate'] ='1';
	if ($returnUrl) $formHiddenInputs['returnUrl'] = $returnUrl;
	if ($abandonUrl) $formHiddenInputs['abandonUrl'] = $abandonUrl;
	ksort($formHiddenInputs);
	$stringToSign = "";

	foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
		$stringToSign = $stringToSign . $formHiddenInputName . $formHiddenInputValue;
	}

	$formHiddenInputs['signature'] = getSignature($stringToSign, $acAmazonFPSSecret);
	$pipeline = $acAmazonPayUrl;
	$form = "<form action='$pipeline' method=\"post\">\n";
	foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
		$form = $form . "<input type=\"hidden\" name=\"$formHiddenInputName\" value=\"$formHiddenInputValue\" />\n";
	}
	$form = $form . "<input type='image' alt='pay via Amazon'
	                                src='https://authorize.payments.amazon.com/pba/images/payNowButton.png' border='0' />";
	$form = $form . "</form>\n";
	return $form;
}

/*
function AmzPayNowButton($price,$bumpcounters, $shortdescription,$longdescription,$reference,$returnUrl, $abandonUrl=false)
{
	$reference = $reference.'-'.$bumpcounters[0].'-'.$bumpcounters[1].'-'.$shortdescription;
  $fpsReturn = gpath('Secure_Url')."/mod/fpscounters.php?next=".urlencode($returnUrl);

	return AmzPayNowButtonForm($price,$shortdescription, $longdescription, $reference, $fpsReturn ,$abandonUrl);
}
 */

/**
 * Create a button for the specified product.
 *
 * @param $productCode - short code for product, key in global $acAmazonProducts
 * @param $billingId - billing token OR mcid of user, if user has no billing id
 */
function AmzPayNowButton($productCode,$billingId,$returnUrl,$abandonUrl=false)
{
  global $acAmazonProducts;

  if(!isset($acAmazonProducts[$productCode]))
    throw new Exception("Bad product code $productCode");

  $product = $acAmazonProducts[$productCode];
  $bumpcounters = $product["counters"];
  $price = $product["price"];
  $shortdescription = $productCode;
  $longdescription = $product["description"];
  
	$reference = $billingId.'-'.$bumpcounters[0].'-'.$bumpcounters[1].'-'.$bumpcounters[2].'-'.$shortdescription;
  $fpsReturn = gpath('Secure_Url')."/mod/fpscounters.php?next=".urlencode($returnUrl);

	return AmzPayNowButtonForm($price,$shortdescription, $longdescription, $reference, $fpsReturn ,$abandonUrl);
}

?>
