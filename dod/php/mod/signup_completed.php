<?php

require_once "modpay.inc.php";

$body = page_header('page_succreg', "Your Registration Was Successful" );
$footer = page_footer();
echo $body;
?>
<div id="ContentBoxInterior" mainTitle="MedCommons Retail Home">
<h2>Registration Successful</h2>
<p>Your registration was successful and a new HealthURL Account has been created for you.</p>
<h2>Next Step</h2>
<p>Your next step is to wait for your verification email to arrive and to click the provided
link in the email to verify your account.</p>
</div>
<?
echo $footer;
?>
