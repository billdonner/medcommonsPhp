<?php
// record suggestions in the db and give adrian an email
require_once "alib.inc.php";

$id = $_REQUEST['id'];
list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in(); // does not return if not lo
$db = aconnect_db(); // connect to the right database
// write to the database
$q = "select * from suggestions where id='$id'";
$result = mysql_query($q) or die("Cant $q".mysql_error());
$r = mysql_fetch_object($result); 
if ($r===false) die ('There is no suggestion with that id');

$buf =  "<h3>Suggestion #".$r->id." ".$r->topic. " ". $r->refer." ".$r->email."</h3>";
$buf .= "<h4>Add External Resources</h4>";
$buf .= $r->addresources;
$buf .= "<h4>Remove External Resources</h4>";
$buf .= $r->remresources;
$buf .= "<h4>Graphics Improvements</h4>";
$buf .= $r->graphics;
$buf .= "<h4>Other Suggestions</h4>";
$buf .= $r->other;



	$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="User Suggestion $id"/>
        <meta name="robots" content="noindex,nofollow"/>
        <title>MedCommons User Suggestion $id</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <style type="text/css" media="all"> @import "main.css";</style> 
        <!-- <style type="text/css" media="all"> @import "acctstyle.css";</style> -->
        <style type="text/css" media="all"> @import "theme.css"; </style>
        <style type="text/css" media="all"> @import "theme.css.php"; </style>
   </head>
    <body id="css-zen-garden">
    <div id="container">
 		$buf
	  </div>
    </body>
    </html>
XXX;
echo $html;
?>

