<?PHP
//handles tracking coming in via email referrals
require "dbparams.inc.php";
require_once "track.inc.php";

$tracking = $_REQUEST['trackingNumber'];

$tracking = str_replace(array(' ','=','?',':','-'),"",$tracking);

tracking_process($tracking);

// if here we have no match
	$url = $_POST['returnurl'];
	$x="<html><head><title>error</title><meta http-equiv='REFRESH' content='0;url=".$url."'></head></html>";
	echo $x;
//error_redirect($url);
?>