<?php
//nothand.php = handler for notification cxp commands -activated for each POST received, process commands
//
// bill donner, MedCommons 28 July 05
//
$not=time();
//make a dirctory to save collateral
mkdir ("notificationdata/not$not");
// get the xml datablock and save it
$xml=urldecode($_POST['xmldata']);
file_put_contents("notificationdata/not$not/not$not.xmldata.xml",$xml);
// get the ccr and save it
$ccr=stripslashes(urldecode($_POST['ccrdata']));
file_put_contents("notificationdata/not$not/not$not.ccrdata.xml",$ccr);


$xmldata = simplexml_load_string($xml);
$opcode = $xmldata->opcode;

$callerskey = $xmldata->callerskey;

//get and save bodies of all attachments

$files = $xmldata->Files;
$count=0;
foreach ($files->File as $file)
{
	$name = $file->FileName;
	$type = $file->FileType;
	$contents = base64_decode($file->FileContents);
	file_put_contents("notificationdata/not$not/$name.$type",$contents);
	$count++;
}

//prepare response as xml
$out = prepare_cxpresponse("cxp","NOTIFICATION",$callerskey,$txn,"INCOMING");

// write out response to file
file_put_contents("notificationdata/not$not/not$not.response.xml",$out);


// finally send it back as an xml resonse
header("Content-Type: text/xml");
echo $out;
?>