<?php
require_once "newslog.inc.php";

$gid = $_REQUEST['gid'];
$filter = $_REQUEST['filter'];
$out = newslog($gid,10,$filter);

$html=<<<XXX
<html><head><title>Incoming NewsReader Log</title>
       <style type='text/css' media='all'> @import 'acctstyle.css';</style>
		</head><body>$out</body></html>
XXX;

echo $html;
		
?>