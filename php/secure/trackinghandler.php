<?PHP
//handles tracking coming in via email referrals
require "dbparams.inc.php";
require_once "track.inc.php";

$tracking = htmlentities($_REQUEST['trackingNumber']);

$tracking = str_replace(array(' ','=','?',':','-'),"",$tracking);

tracking_process($tracking);

// if here we have no match
	$url = $_POST['returnurl'];
	$x="<html><head><title>tracking redirect error</title><meta http-equiv='REFRESH' content='0;url=".$url."'></head>
	<body>no matching, waiting to redirect in tracking handler $url $tracking</body></html>";
echo $x;

//echo "going to url $url";
//error_redirect($url);
?>