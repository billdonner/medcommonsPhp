<?php
//surrounds the body of the tracker view with extra html doodads
require_once "bodyTrackers.inc.php";
$t=tconfirm_logged_in();
if ($t!==false)	
list($accid,$fn,$ln,$email,$idp,$cl)=$t;
else {
$mckey= $_REQUEST['mckey'];
if ($mckey!='')
list ($sha1,$accid,$email)=explode('|',base64_decode($mckey));
else {
die ("Must be logged on or supply MedCommons Key to use trackers");

}
}
$html= <<<XXX
<html><head><title>MedCommons - My Trackers</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "gadget.css"; </style>
</head>
XXX;

$html.=bodyTrackers($accid,'.',
false, // not running on the account page
"", //editor window inline
"",// input window
"_help" // help
);

$html.= <<<XXX
</html>
XXX;

echo $html;
?>