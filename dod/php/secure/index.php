<?PHP
require "dbparams.inc.php";
require_once "track.inc.php";
// look up the tracking number to 
if (!isset($_REQUEST['healthURL'])) die("Must supply healthURL");
$tracking = $_REQUEST['healthURL'];
// lookup by tracking number 
tracking_process($tracking);
// if here we have no match
$x = <<<XXX
<html>
<head>
<title>MedCommons healthURL Lookup Error</title>
</head><body>
<p>
Regrettably, we are unable to locate  healthURL $tracking
</body>
</html>
XXX;
echo $x;
?>