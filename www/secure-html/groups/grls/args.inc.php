<?php 
function cleanreq ($x) { if (isset($_REQUEST[$x])) return $_REQUEST[$x]; else return false;}
// these are the basic query parameter arguments that are passed around
//
//
$pfn = cleanreq('PatientFamilyName');
$pgn = cleanreq('PatientGivenName');
$pid= cleanreq('PatientIdentifier');
$pis= cleanreq('PatientIdentifierSource');
$psx = cleanreq('PatientSex');
$pag = cleanreq('PatientAge');
$spid  = cleanreq('SenderProviderId');
$rpid  = cleanreq('ReceiverProviderId');
$dob  = cleanreq('DOB');
$cc = cleanreq('ConfirmationCode');
// these are not query parameters, but are passed around
$rs = cleanreq('RegistrySecret');
$guid = cleanreq('Guid');
$purp = cleanreq('Purpose');
$cxpserv = cleanreq('CXPServerURL');
$cxpvendor = cleanreq('CXPServerVendor');
$viewerurl = cleanreq('ViewerURL');
$comment = cleanreq('Comment');
// these params control the formatting of output
$int = cleanreq('int'); // if non-zero, ajax'd dynamic updates
$st = cleanreq('st');
$ti = cleanreq('ti');
$limit = cleanreq('limit');
$logo = cleanreq('logo');
// this multiplexes the group - wld 072506
$gid = cleanreq('gid');

?>
