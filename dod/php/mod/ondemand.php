<?php

require_once "modpay.inc.php";

$header = page_header_cond( "page_how","MedCommons on Demand");
$footer = file_get_contents('page_footer().html');

if (testif_logged_in()!==false) $signin ="<h3>You are currently signed on to MedCommons on Demand</h3>";
else $signin = <<<XXX
<h3>Sign In</h3>
<form method='post' action='../acct/login.php' name='login' id='login'>
  <input type='hidden' name='next' value='../mod/svcsetup.php' />
  <input type='hidden' id='idptype' name='idptype' value=''/>

<div class=f><span class=n>Email/MCID</span><span class=q>
<input type='text' name='mcid' id='mcid'
              /><span class=r>or enter a full openid</span>
</span></div>
  
  <div class=f><span class=n>Password</span><span class=q>
<input  type='password' name='password' id='password'     /><span class=r> </span>
</span></div>

  <div class=f><span class=n>&nbsp;</span><span class=q>
<input type=submit value='Sign In' /><span class=r>&nbsp;</span>
</span></div>

<br/>
</form>

XXX;


echo <<<XXX
$header
<div id="ContentBoxInterior"    mainId='page_register' mainTitle="Offer Convenient Health Information Services"  >
<h2>MedCommons on Demand</h2>
<img id=voucherimg alt=voucherimg src="http://www.medcommons.net/images/Voucher_200.png" />
<p>
MedCommons on Demand vouchers allow you to charge the client for your information-based services such as records release and diagnostic imaging.
Vouchers are linked to temporary HealthURLs that expire automatically after a period you specify.
Vouchers can also be free for services that just require simple, HIPAA-compliant patient communications on the Web.
Vouchers can be created in seconds from our templates or custom designed to carry your instructions,logo and branding.



</div>

<table class=tinst >
<tbody >
<tr ><td class=lcol >Instructions</td><td class=rcol >
Service vouchers are available only to Registered users (<a class=subscribe href=personal.php>Sign In</a>)
<br/> After registration, please Sign In and visit your Settings page to enable MedCommons On Demand. 
<br/>You will be able to define custom vouchers with your logo and design and collect payment for your information-based services through Amazon Payments.</td></tr>
</tbody>
</table>
</div>
$footer
XXX;
?>
