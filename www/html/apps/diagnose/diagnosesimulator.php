<?php

$html = <<<XXX
<html><head><title>Cina Simulator</title></head>
<body>
<img src=http://cina-us.com/assets/images/logo.gif alt=cinalogo />
<h3>Supply your CCR to CINA for Instant Analysis</h3>

<!-- The data encoding type, enctype, MUST be specified as below -->
<form action="cinadone.php" method="POST">
<p>By supplying a Tracking Number to CINA, we can identify which CCR you want analyzed. 
</p><p>
If you want MedCommons to aggregate a 
CCR representing your overall health hit 'Submit to Cina' without a number</p>
	<input type=text name=tracking />
    <input type="submit" value="Submit to CINA" />
</form>
</body></html>
XXX;

echo $html;
?>