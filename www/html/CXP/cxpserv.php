<?php
//cxpserv2.php = handle cxp commands -activated for each POST received, process commands
//
// bill donner, MedCommons 28 July 05
//
require_once "cxplib.inc.php";
//

	function getactorinfo($xml)
{	
	$name = $xml->Name;
	$cn = $name->CurrentName;
	return "<Given>".$cn->Given."</Given><Middle>".$cn->Middle."</Middle><Family>".$cn->Family."</Family>";
}
// start here with each request to be handled by the cxpserver
//log everything received and sent in the local file system
//make a random transaction number
$txn = rand(100000,999999).rand(100000,999999);
//make a dirctory to save collateral
mkdir ("serverdata/txn$txn");
// get the xml datablock and save it
$xml=urldecode($_POST['xmldata']);
//if there is no xmldatablock then treat this as a simple transfer of a plain old ccr
if ($xml=="") 
{
	
	$opcode = "IMPLIEDTRANSFER";

	
	$ccr=stripslashes(urldecode($_POST['ccrdata']));// hack should say ccr
	$len=strlen($ccr);
	if ($len==0) $response = "ZERO LENGTH CCR";
	else {
	file_put_contents("serverdata/txn$txn/txn$txn.ccrdata.xml",$ccr);
	$ccrdata = simplexml_load_string($ccr);
	if ($ccrdata===false) $response = "ILL FORMED CCR";
	else{
	$dt = $ccrdata->DateTime->ExactDateTime;
	//dig the patient and provider out of the CCR
	$patientlink = $ccrdata->Patient->ActorID; 
	$patientinfo="not found";
	$fromlink = $ccrdata->From->ActorLink->ActorID; 
	$frominfo="not found";
	//now go thru all the actors, and figure out who is whom
		$actors = $ccrdata->Actors;

	foreach ( $actors->Actor as $actor)
	{
				
	switch ($actor->ActorObjectID){
	case $patientlink: $patientinfo = getactorinfo($actor->Person); break;
	case $fromlink: $frominfo = getactorinfo($actor->Person); break;
	default :
	}
	}


	$callerskey ="<CCRDocumentObjectID>$ccrdata->CCRDocumentObjectID</CCRDocumentObjectID>"."\r\n".
	             "<ExactDateTime>$dt</ExactDateTime>\r\n".
	               "<Patient>$patientinfo</Patient>\r\n".
	                 "<From>$frominfo</From>\r\n";
	$response = "TRANSFERDONE";
	}
}}

else { //there is an xml data block present
file_put_contents("serverdata/txn$txn/txn$txn.xmldata.xml",$xml);
// parse the xmldatablock
$xmldata = simplexml_load_string($xml);

$opcode = $xmldata->opcode;

// get the callers key, so we can give it back to him
$callerskey = $xmldata->CallersKey;
//notification address

// dispatch based on opcode
switch ($opcode) {
	case "TRANSFER":
	// get the ccr and save it
	$ccr=stripslashes(urldecode($_POST['ccrdata']));
	file_put_contents("serverdata/txn$txn/txn$txn.ccrdata.xml",$ccr);

	//get and save bodies of all attachments
	$files = $xmldata->Files;
	$attachments = "";$count=0;
	foreach ($files->File as $file)
	{
		$name = $file->FileName;
		$type = $file->FileType;
		$contents = base64_decode($file->FileContents);
		$contlen = strlen($contents);
		file_put_contents("serverdata/txn$txn/$name.$type",$contents);
		$attachments.="<attachment id='$count' size='$contlen'><name>$name.$type</name></attachment>";
		$count++;
	}
	$response = "TRANSFERDONE ";
	// write a sentinal file to indicate we finished a complete set so they all exist
	break;

	case "QUERY":
	$notaddr = $xmldata->QueryNotificationURL;
	$txn = $xmldata->QueryString;
	//	echo "looking for $txn";
	$str = @file_get_contents("serverdata/txn$txn.txn");
	if ($str===false) $response = "QUERYFAIL $txn $str"; else
	$response = "QUERYDONE $txn $str";
	// if notification then do it
	if ($notaddr!="") { //ignoring https for now
	$response.=$notaddr;
	$xml = file_get_contents("serverdata/txn$txn/txn$txn.xmldata.xml");
	$ccr = file_get_contents("serverdata/txn$txn/txn$txn.ccrdata.xml");
	parseurl($notaddr,$prot,$host,$port,$file);
	postcall($host,$port,$file,urlencode($xml),urlencode($ccr));
	}

	break;

	default: $response = "UNKNOWN $response";

}
} // end of xml datablock processing
//prepare response as xml
	file_put_contents("serverdata/txn$txn.txn","");



$out = prepare_cxpresponse("cxp",$opcode,$callerskey,$txn,$response);


// write out response to file
file_put_contents("serverdata/txn$txn/txn$txn.response.xml",$out);


// finally send it back as an xml resonse
header("Content-Type: text/xml");
echo $out;
?>