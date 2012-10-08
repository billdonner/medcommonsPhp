<?php

require_once "modpay.inc.php";
$loggedin  = testif_logged_in();

$devpay_url = $GLOBALS['devpay_redir']."?src=".urlencode($GLOBALS['mod_base_url']."/payment_processed.php");
if ($loggedin!==false) {
$purchase = "<span class=big>Purchase</span> a MedCommons HealthURL Subscription <small>(you already have a subscription)</small>";
}
else 
{
	$purchase = "<span class=big>Purchase</span> a MedCommons HealthURL <a href='$devpay_url'>Subscription</a>";
}

$header = page_header("page_personal", "How to Get a Personal MedCommons Account" );
$footer = file_get_contents('page_footer().html');


echo <<<XXX
$header
<div id="ContentBoxInterior" mainTitle="Instructions for Getting A MedCommons Account">
<h2><b>Get a MedCommons HealthURL Account</b></h2>
<p>5 easy steps:
<ol>
<li><b>Review</b> the MedCommons <a href='http://www.medcommons.net/termsofuse.html' >Terms of Use</a> and MedCommons.net <a href='http://www.medcommons.net/privacy.html'>Privacy Policy</a></li>
<li>$purchase  <img src='https://images-na.ssl-images-amazon.com/images/G/01/webservices/amzPayments_logo_small._V29520543_.gif' /></li>
<li><b>Inquire</b> about your bill at <a href='mailto:application-payments@amazon.com' >application-payments@amazon.com</a></li>
<li><b>View</b> the Amazon <a href='http://www.amazon.com/dp-applications' >Application Billing Page</a></li>
<li><b>Visit</b> the <a href='http://forum.medcommons.net/'>MedCommons Forum</a> for news and support
</ol></p>
</div>
$footer
XXX;
?>
