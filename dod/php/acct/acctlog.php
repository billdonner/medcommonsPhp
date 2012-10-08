<?php
require_once "alib.inc.php";
require_once "acctlog.inc.php";

$gid = $_REQUEST['gid'];
$filter = $_REQUEST['filter'];
$out = acctlog($gid,10,$filter);

$html=<<<XXX
<html><head><title>Identity Service Account Log</title>
       <style type='text/css' media='all'> @import 'acctstyle.css';</style>
		</head><body>$out</body></html>
XXX;

echo $html;
		
?>