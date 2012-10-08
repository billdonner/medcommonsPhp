<?php
// record suggestions in the db and give adrian an email
require_once "tlib.inc.php";

$id = $_REQUEST['id'];
list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in(); // does not return if not lo
$db = aconnect_db(); // connect to the right database
// write to the database
$q = "select * from editrequests where id='$id'";
$result = mysql_query($q) or die("Cant $q".mysql_error());
$r = mysql_fetch_object($result); 
if ($r===false) die ('There is no editrequests with that id');

$buf =  "<h3>Editorial Control Request #".$r->id." for topic ".$r->topic. " from ".$r->email."</h3>";
$buf .= "<h4>screenname</h4>";
$buf .= $r->screenname;
$buf .= "<h4>other info supplied by this user</h4>";

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

