<?php 
// ajax server side call to enter/exit account lock mode
// send back only those updates needed to bring the screen up to date {
require_once "dbparamsidentity.inc.php";
require_once "ccrloglib.inc.php"; // the hard work is all in here

$lock = $_GET['lock']; // get last time ajax client heard from us
$noedit =($lock==1);
$accid = $_GET['accid']; // get accountid
$synch = time();

$emit = "<ajblock>";

$count = readdb($mini,$accid,$from,$content,$tab,$emailbuf,$fn,$ln,$email,$street1,$street2,
$city,$state,$postcode,$country,$mobile,$emergencyccr,$patientcard,$einfo,$trackerdb);
  
// put together tab0, must get count and tabs anyway
if ($mini == false) $tab0content = tab0($noedit,$email,$mobile,$ln,$fn,$street1,$street2,$city,$state,$postcode);
// assemble all the tabs
$alltabs = assembletabs(false, $count,$content,$tab,$tab0content);
$emit .= "<content>$alltabs</content>";

// echo back the whole div

$emit .="<timesynch>$synch</timesynch><status>tabs</status></ajblock>";


echo $emit;

exit;


?>
