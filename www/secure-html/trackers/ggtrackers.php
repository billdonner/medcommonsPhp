<?php
// give the google gadget just what it wants, with no decoration
require_once "bodyTrackers.inc.php";


$mckey= $_REQUEST['up_mckey'];
if ($mckey!='')
list ($sha1,$accid,$email)=explode('|',base64_decode($mckey));
else {
die ("up_mckey must be set by Google");

}


$html= <<<XXX
<html><head><title>MedCommons - My Trackers Running as Google Gadget</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "gadget.css"; </style>
</head>
XXX;

$html.= 
bodyTrackers($accid,'.',
false, // not running on account page
"_new", //editor window
"_new",// input window
"_helpwindow" // help
);

echo $html."</html>";;
?>