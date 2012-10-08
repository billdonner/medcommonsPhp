<?PHP
require "dbparams.inc.php";
require_once "track.inc.php";
require 'mc.inc.php';

// get callback parameter
if (isset($_REQUEST['p']))
  $p = $_REQUEST['p']; 
else
  $p = '';

// look up the tracking number 
$tracking = clean_tracking_number($_REQUEST['q']);
// lookup by tracking number 
tracking_process($tracking);
// if here we have no match
$Pos = strpos($p,'&error=');
if ($Pos>0) $p=substr($p,0,$Pos);

if(isset($_SERVER['HTTP_REFERER'])) {
  $url =$_SERVER['HTTP_REFERER']."?p=$p&error=Invalid%20Tracking%20Number";
  header ("Location: $url");
  exit;
}
?>
<html>
<body>
  <h4>MedCommons Error</h4>
  <p>Apologies, your tracking number could not be resolved.</p>
</body>
</html>
