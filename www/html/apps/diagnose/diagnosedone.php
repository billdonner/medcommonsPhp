<?php

$diagnoses =  array('You seem to be in fine shape',
					"If I had a body like yours, I'd be in show business",
					'Take two asprin and run me again in the morning',
					'The CCR seems fine, but what about the patient?',
					"I'm suggesting you go to your Primary Care Physisican",
					"There's nothing wrong with you that two weeks vacation won't fix");
					
$count = count($diagnoses);

$rand = rand(0,$count-1);


$slogan = $diagnoses[$rand];

$html = <<<XXX
<html><head><title>Cina Simulator</title></head>
<body>
<img src=http://cina-us.com/assets/images/logo.gif alt=cinalogo />
<h3>Your Instant Analysis from CINA</h3>
<p>
$slogan
</p>
</body></html>
XXX;

echo $html;



exit;

?>