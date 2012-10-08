<?php

require_once "modpay.inc.php";
$v->err = $v-> voucherid_err = $v->otp_err = $v->otp = $v->voucherid = '&nbsp;';
$header = page_header("page_customize" ,"Customize a Voucher" );
$footer = page_footer();
$markup = <<<XXX
$header
<div id="ContentBoxInterior" " mainTitle="Activate a MedCommons Voucher">
<h2>Customizing the Live Voucher Format</h2>
<p><a href = "/images/livevoucher.gif" target="_blank" >Here's an image</a> of the live voucher we'll be dissecting</p>
<h3>The Process</h3>
<p>You will supply us with a template that we will use to allow you to view and print vouchers. 
The template should include CSS and external images as desired but no javascript</p>
<ul>
<li>Construct a basic HTML template with your branding logos, and any special instrucitons for you and your staff. 
This block should be surrounded by <div class='coupon'>. Many HTML constructs are excluded for safety reasons.This page is not seen by patients.</li>
<li>Include special  ***SYMBOLS*** representing different components in your html markup</li>
<li>Paste the template into the Service definition LIVE-VOUCHER-TEMPLATE input field</li>
</ul>
<h3>A Sample Live Voucher Page  Template</h3>
<xmp><div class='coupon'>
<img src="http://www.logodesignpros.com/Designs/LG-40098/609_40098_LOGO_S.jpg">
<h3>Your Patient Will See: Your Sonogram from Dr. Dan</h3>
**ST**
<p>Go to http://www.drdan.net/ to pick up your Sonogram</p>
**CR**
<h3>PRINT A VOUCHER TO GIVE TO PATIENT</h3>
**PV**
<h3>ASK PATIENT TO PAY NOW IF POSSIBLE</h3>
**PN**
</div></xmp>
<h2>Customizing the Printed  Voucher  Page Format</h2>
<p><a href = "/images/printedvoucher.gif" target="_blank" >Here's an image</a> of the printed  voucher we'll be dissecting</p>
<h3>The Process</h3>
<ul>
<li>Use the same or similar template. Be sure not to put inappropriate material in your tempate. This will be printed and  may be copied and 
freely distributed,</li>
<li>Include special  ***SYMBOLS*** representing different components in your html markup</li>
<li>Paste the template into the Service definition PRINTED-VOUCHER-TEMPLATE input field</li>
</ul>
<h3>A Sample Printed Voucher Template</h3>
<xmp><div class='coupon'>
<img src="http://www.logodesignpros.com/Designs/LG-40098/609_40098_LOGO_S.jpg">
<h3>Your Sonogram from Dr. Dan</h3>
**ST**
<p>Go to http://www.drdan.net/ to pick up your Sonogram</p>
**CR**
<h3>You can use this as a fax cover sheet</h3>
**FC**
</div></xmp>
<h2>The Pieces</h2>

<h4>**ST** Standard Voucher Info</h4>
<div class=custompiece>
<p>Place the symbol  **ST** in your HTML to include a standard block describing the voucher</p>
<img border=0 width=600 src="/images/stvoucherinfo.gif" />
</div>

<h4>**CR** Credentials</h4>
<div class=custompiece>
<p>Place the symbol  **CR** in your HTML to include a standard block describing the credentials needed to gain access to medical records</p>
<img border=0 src="/images/credentials.gif" />
</div>

<h4>**PV** Print Voucher Button</h4>
<div class=custompiece>
<p>Place the symbol **PV**  in your HTML to include a Print Voucher button on the live voucher</p>
<img border=0 src="/images/printvoucher.gif" />
</div>

<h4>**PN** Optional Pay Now Button</h4>
<div class=custompiece>
<p>Place the symbol **PN**  in your HTML to include a Pay Now button on the live page to allow immediate payment of Vouchers over the counter</p>
<img border=0 src="/images/optpaynow.gif" />
</div>

<h4>**XT** Optional Extras Section</h4>
<div class=custompiece>
<p>Place the symbol **XT**  in your HTML to include a section of additional information</p>
<img border=0 src="/images/optextras.gif" />
</div>

<h4>**FC** Optional Fax Cover Sheet</h4>
<div class=custompiece>
<p>Place the symbol **FC**  in your HTML to include a small fax cover sheet on printed vouchers</p>
<img border=0 src="/images/optfaxcover.gif" />
</div>
$footer
XXX;
echo $markup;
?>