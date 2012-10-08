<?PHP
require "dbparams.inc.php";
require_once "track.inc.php";
// look up the tracking number to 
$tracking = $_REQUEST['a'];
// lookup by tracking number 
tracking_process($tracking);
// if here we have no match
$x = <<<XXX
<html>
<head>
<title>MedCommons eReferral Redirect Error</title>
</head><body>
<p>
Regrettably, we are unable to locate CCR $tracking from your email invite
</body>
</html>
XXX;
echo $x;
?>