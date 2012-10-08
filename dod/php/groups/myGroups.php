<?php

// show all the group stuff
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

require_once "mygroups.inc.php";

$body1 = my_groups($accid);
$body2 = my_admingroups($accid);
$body3 = my_providers($accid);
$body4 = my_practices($accid);
$body5 = my_adminpractices($accid);


$trailer = "<div><p><small>Proceed to the standard 
<a href=http://medcommons.net/ >Start Page</a><small></p></div>";
// finally, dump it out

$x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
     <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="My MedCommons Groups"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons Groups</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "groups.css"; </style>
    </head> 
                <table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="Show Groups" /></a>
                </td><td>My MedCommons Groups<small> 
                &nbsp;<a href=myGroups.php>refresh</a>&nbsp;
                <a href=../acct/goStart.php>home</a>&nbsp;

				acct $accid $email
				</small></td></tr>
				</table><h3>Groups Where I am an Ordinary Member</h3>$body1
				<h3>Groups Where I am an Administrator</h3>$body2
				<h3>Practices Where I am a Patient</h3>$body3
				<h3>Practices Where I am a Provider</h3>$body4
				<h3>Practices Where I am an Administrator</h3>$body5
				
				<p>$trailer</p>
    </body>
</html>
XXX;
echo $x;

?>
