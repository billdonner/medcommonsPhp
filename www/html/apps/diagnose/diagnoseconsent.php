<?php
$gurl = $GLOBALS['Extensions_Url']."appservices.php";
$html = <<<XXX
<html><head><title>Cina Simulator</title></head>
<body>
<img src=http://cina-us.com/assets/images/logo.gif alt=cinalogo />
<h3>Bilateral Consent Between CINA and Patient</h3>
<p>
You, the patient, agree to hold both CINA and MedCommons harmless, regardless of whatever you may otherwise believe.

<form action=$gurl>
<input type=submit value='Ok'>
</form>
</p>
</body></html>
XXX;

echo $html;



exit;

?>