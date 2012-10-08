<?PHP
//we are going to get redirected to this from the master dispatcher when presented with a tracking number
require "dbparams.inc.php";
require_once "track.inc.php";
// look up the tracking number to 
$tracking = htmlentities($_REQUEST['a']);
// lookup by tracking number 
tracking_process($tracking);
// if here we have no match
$x = <<<XXX
<html>
<head>
<title>MedCommons Cant Find Item With That Tracking Number</title>
</head><body>
<p>
Regrettably, we are unable to locate any CCR with that tracking number
</body>
</html>
XXX;
echo $x;
?>