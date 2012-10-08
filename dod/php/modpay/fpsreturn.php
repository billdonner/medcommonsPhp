<?php
require_once "setup.inc.php";


function  wsAdjustCounters($btk,$faxin,$dicom,$acc) {
	$REMOTE_WSCOUNTERS_SERVICE = $GLOBALS['remote_wscounters_service'] ;
	$str = file_get_contents("$REMOTE_WSCOUNTERS_SERVICE?btk=$btk&faxin=$faxin&dicom=$dicom&acc=$acc");
	$xml = simplexml_load_string($str);
	$counters = $xml->counters;
	$status = $counters->status;
	if ($status!='1') return false;
	return array ($faxin,$dicom,$acc);
}

$status = $_GET['status'];

if ($status == 'PS')
{
	//if we bought some counters then bump them
	$v = explode('-',$_GET['referenceId']);
	$c = explode(',',$v[1]);
	$stat = wsAdjustCounters($v[2],$c[0],$c[1],$c[2]);
	$product = $v[0];
	$disp = ($stat?1:0);
	$btk = $v[2];
	if (substr($product,0,3)=='MMC')
	{
	
	//echo "Adjusted counters for product ".$v[0]." billing token ".$v[2]." as directed<br/>";
	//echo "Proceed on to <a href='../acct/register.php' >Step 2 - Tell us More about Yourself</a><br/>";
	header("Location: ".$GLOBALS['remote_wscounters_service'] ."hijacked_register_one.php");
	echo "Redirecting  to registration process";
	} 
	else 
	{
	header("Location: ".$GLOBALS['fps_purchase_done']);
	echo "Redirecting  to appliance home";	
	}
}
else 
{
	$header = <<<XXX
<html><head><title>MedCommons FPS Product Catalog</title>
     <link media='all'
	href='http://www.medcommons.net/medCommonsStyles.css'  type='text/css' rel='stylesheet' /></head>
    <body><img border='0' src='https://www.medcommons.net/images/HP_logo.jpg' /><h1>MedCommons FPS Product Catalog</h1>
    <div style='float:left; margin:10px;' >
XXX;

echo $header.
'<h2>Unusual Return from Amazon FPS Pipeline</h2>';
echo '<p>status: '.$_GET['status'].'</p>';
if (isset($_GET['referenceId']))
echo '<p>referenceId: '.$_GET['referenceId'].'</p>';
if (isset($_GET['transactionId']))
echo '<p>transactionId: '.$_GET['transactionId'].'</p>';
	echo "Unusual status did not cause any counters to be adjusted";
	echo "<p><a href=catalog.php>make another payment</a></p>";
}
echo "
 <img src=https://images-na.ssl-images-amazon.com/images/G/01/webservices/AWS_LOGO._V2289989_.gif  border='0' />
    </body>
</html>";

?>
