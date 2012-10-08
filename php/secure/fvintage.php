<?php
if (isset($_REQUEST['submit']))
{
require "../dbparams.inc.php";
require_once "track.inc.php";
// look up the tracking number to 
$tracking = $_REQUEST['a'];
$tracking = str_replace(array(' ','	','.'),array('','',''),$tracking);
// lookup by tracking number 
tracking_process($tracking);
// if here we have no match
$x = <<<XXX
<html>
<head>
<title>MedCommons Vintage Repository Lookup</title>
</head><body>
<p>
Regrettably, we are unable to locate a CCR with $tracking as its Tracking Number&nbsp;
<a href='http://www.medcommons.net/vintage.php'>try again</a>

</body>
</html>
XXX;
echo $x;
exit;
}
?>