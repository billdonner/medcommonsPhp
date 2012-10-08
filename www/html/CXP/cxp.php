<?PHP
require_once "cxplib.inc.php";

function prepare_xmldatablock($oc)
{
	return "<xmldata><opcode>$oc</opcode>";
}
function property($tag,$url)
{
	return"<$tag>$url</$tag>";
}
function attach($file,$type,$name)
{
	$s = @file_get_contents($file);
	if ($s===false) return false;

	$z = base64_encode($s);
	return "<File><FileName>$name</FileName><FileType>$type</FileType><FileContents>$z</FileContents></File>";
}

$configfile = $_REQUEST['cfg'];
if ($configfile=="")$configfile = "config.xml";

// open a config file which has our complete instructions

$xml = simplexml_load_file($configfile);
$repeatcount = $xml->repeatcount;
if ($repeatcount <1) $repeatcount = 1;
$repeatinterval = $xml->repeatinterval;
if ($repeatinterval <1) $repeatinterval = 0;
$remoteurl = $xml->remoteurl;
$callerskey = $xml->callerskey;
$notificationurl = $xml->notificationurl;
$querynotificationurl = $xml->querynotificationurl;
$querystring = $xml->querystring;

$ccrfile = $xml->ccrfile;
$opcode = $xml->opcode;
// get address of remote part
if (parseurl ($remoteurl,$prot,$host,$port,$filespec)=== false)
echo ("Can't parse remote url - should return xml error");
// start to assemble the xml data block as per the spec
// if no opcode then don't make an xmldatablock
if ($opcode !="")
{
$xmldata = prepare_xmldatablock($opcode);
	$xmldata .= property("CallersKey",$callerskey);

switch ($opcode) {

	case "TRANSFER":
	$xmldata .= property("NotificationURL",$notificationurl);

	$xmldata .= "<Files>";
	$attachments = $xml->attachments;
	// echo "attachments $attachments";

	foreach ($attachments->attachment as $attachment )
	{
		$s = attach ($attachment,
							$attachment['type'],
							$attachment['link']);
		if ($s===false) echo "can't attach $attachment\r\n";
		else
		$xmldata .=$s;

	}
	$xmldata .="</Files></xmldata>";
	break;
	
	case "QUERY":
	$xmldata .= property("QueryNotificationURL",$querynotificationurl);
	$xmldata .= property("QueryString",$querystring);
	$xmldata .="</xmldata>";

	break;
	default:
}


$xmlencoded = urlencode($xmldata);

} // end of case of building xml data block
else 
{
	// no xmldatablock
$xmlencoded = "";
}
	
$ccrdata = file_get_contents($ccrfile);
$ccrencoded = urlencode($ccrdata);


$request = time();


//make a button that can be pasted
$form = form ($host,$port,$filespec,$xmlencoded,$ccrencoded);
//save a button version of the form
file_put_contents("clientdata/req$request.button.html",$form);
//
//just echo the form to show the button version
//echo $form;
//exit;
//if here, then execute the remote call
$ret = "";
for ($i=1; $i<=$repeatcount; $i++){
	$response = postcall($host,$port,$filespec,$xmlencoded,$ccrencoded);
	//echo $form; dont echo, instead contact inline
	// write out the xml
	file_put_contents("clientdata/req$request.response.xml",$response);
	$ret .= $response;
	$request = time();//new file next time around
}
//echo this back
if ($repeatinterval>0)
header("Refresh: $repeatinterval; URL=cxp.php");
header("Content-Type: text/xml");
echo "<cxpresponses>$ret</cxpresponses>";
?>