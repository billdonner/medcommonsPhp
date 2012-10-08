<?php
//
// load a file of files, then read each individual file and write to the appservices table
//
$arg = $_REQUEST['a'];
if ($arg=='') die('usage: appviewall');

$html= <<<XXX
<html><head><title>MedCommons - View All App Services</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "appsrv.css"; </style>
</head>
<body>
<table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="View App Services" /></a>
                </td><td>View App Service Description<small> <i>more soon</i>
                &nbsp;<a href = '$arg' target="_new">view as xml</a></small></td></tr>
                </table><table class='trackertable'><tr><th> </th><th> </th></tr>              
XXX;

echo $html;

for ($i=0; $i<$count; $i++)
{
	$xmldoc = simplexml_load_file($files[$i]);

	$svc=$xmldoc->service;
	require "appview.inc.php";
	purchaseinfo($accid);
}
echo "</table>";
echo "</body></html>";


?>

