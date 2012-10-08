<?php

// process CVS blood pressure files

$mckey= $_REQUEST['mckey'];

if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
	$str= file_get_contents($_FILES['userfile']['tmp_name']);
} else {
	echo "File upload failure: ";
	echo "filename '". $_FILES['userfile']['name'] . "'.";
	exit;
}

$xml = simplexml_load_string( $str);
$dtl = $xml->DataTransferLog; // use the checksum
$recid = $dtl->RecID;
$numrecs = $dtl->NumofRecord;
$id = $dtl->ID;

$Patient = $xml->Patient;
$counter = 0;
foreach ($xml->MeasureRec as $mrec) {
	if ($mrec->ID != $id) die ("ID mismatch");
	//echo "Fileid $recid ID is ".$Patient->ID." MatchKey is ".
	$Patient->GivenNames.','.$Patient->FamilyName.','.$Patient->PhoneEmail." ";
	//echo "RecID is ".$mrec->RecID." Sys is ".$mrec->Sys." Dia is ".$mrec->Dia."<br>";
	
	$pipe = "$recid|".$Patient->ID."|".
	$Patient->GivenNames.'|'.$Patient->FamilyName.'|'.$Patient->PhoneEmail."|".
	$mrec->RecID."|".$mrec->Sys."|".$mrec->Dia;
	$url=  "Vx.php?a=$pipe&c=cxp&t=1266182119884458:cvsbpm&mckey=$mckey";
	$counter++;
	if ($counter == $numrecs) { // this is all a bit of a cheat, we should probably handle each record
				header("Location: $url");
		echo ("Redirecting to $url<br><br>");
		exit;
	}
	
}
if ($counter!=$numrecs)
	die ("Inconsistency in number of records")

?>