<?php

echo 
'<h2>Abandon Return from Amazon FPS Pipeline</h2>';
echo '<p>status: '.$_GET['status'].'</p>';
if (isset($_GET['referenceId']))
echo '<p>referenceId: '.$_GET['referenceId'].'</p>';
if (isset($_GET['transactionId']))
echo '<p>transactionId: '.$_GET['transactionId'].'</p>';
echo '<p><a href=index.php>make another payment</a></p>';

?>
