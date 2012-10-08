<?php
//acct.php?fn=Jane&ln=Hernandez&email=jhernandez@foo.com etc&accid=12123123&from=StMungo

	
/*** start of main program ***/
$fn=$_REQUEST['fn'];
$ln=$_REQUEST['ln'];
$email=$_REQUEST['email'];
$accid=$_REQUEST['accid'];
$from=stripslashes($_REQUEST['from']);

/* regrettably, the fancy css to do this all in xml doesn't work on IE, so we need to generate table rows */
$args="fn=$fn&ln=$ln&email=$email&accid=$accid&from=$from";

if (($from=='')||($from=='MedCommons')) $loc = "myccrlogview.php?$args"; else $loc = "patientlistview.php?$args";


header("Location: $loc");
echo "Redirecting to $loc";
exit();

?>