<?php
//
// data on call unifax post handler for arriving faxes
//
// incoming faxes are written to the file system as .pdf or .tiff files (control over format depends on unifax settings)
// bill donner 15 March 2006
///
// based on unifax users guide.pdf Revision Date: 04/15/2005
//

function statushandler($str)
{
	$now = $GLOBALS['now'];
	$reporoot = $GLOBALS['reporoot'];
	// write an error log to a separate file and exit
	file_put_contents($reporoot."statusdata.txt","dataoncallfaxposthandler: ".$str);
// finally send back the response to the external service
header("Content-Type: text/html");
echo "Post Successful";
exit;

}


$now=time();
$GLOBALS['now']=$now; // for use above;
$reporoot = "incomingfaxdata/$now.";
$GLOBALS['reporoot']=$reporoot;
// get the xml datablock and save it
$xml=urldecode($_REQUEST['xml']);
if ($xml=='') statushandler('No incoming xml data block');
//
$xml = stripslashes($xml); // remove the escaping
file_put_contents($reporoot."xmldata.xml",$xml);

// shred the xml for extra 

$xmldata = @simplexml_load_string($xml);
if ($xmldata=='') statushandler('cant parse incoming xml data block');

$fax = $xmldata->FaxControl;
if ($fax=='') statushandler('No incoming fax data block');

$AccountID = $fax->AccountID;
$DateReceived = $fax->DateReceived;
$FaxName = $fax->FaxName;
$FileType = $fax->FileType;
$PageCount = $fax ->PageCount;
$CSID = $fax ->CSID;
$Status= $fax->Status;
$MCFID=$fax ->MCFID;

$bc = $fax->BarcodeControl;

	$barcodesread = $bc->BarcodesRead;
	if ($barcodesread>0) {
	$barcodes = $bc ->Barcodes;
	$barcode = $barcodes->Barcode;
	$bar=$barcode ->Key ;
} else $bar = "nobc";

	$FileContents = $fax->FileContents; // base 64 encoded	
	$RepoFileName = $reporoot.$bar.".".$CSID.".".$FaxName.".".$FileType;
	$contents = base64_decode($FileContents);
	file_put_contents($RepoFileName,$contents);

// finally send back the response to the external service
header("Content-Type: text/html");
echo "Post Successful";
// write a status file to say we got thru successfully
statushandler("successful receive of $RepoFileName");
?>