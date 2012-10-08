<?php
require_once "dbparamspay.inc.php";
require_once "payviacc.inc.php"; 
$err = $_REQUEST['err'];
$x = frontmatter('Thank you for your purchase');
echo $x."<br><small>Thank you for using MedCommons</small></body></html>";
?>
