<?php
require_once "alib.inc.php";
require_once "auditlog.inc.php";

$gid = $_REQUEST['gid'];
$filter = $_REQUEST['filter'];
$out = auditlog($gid,30,'');

$html=<<<XXX
<html><head><title>Audit Log</title>
       <style type='text/css' media='all'> @import 'acctstyle.css';</style>
		</head><body>$out</body></html>
XXX;

echo $html;
		
?>