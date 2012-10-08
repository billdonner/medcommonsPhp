<?php

require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = $_POST['id'];
confirm_admin_access($accid,$id); // does not return if this user is not a group admin

$name = $_POST['property'];



$timenow=time();

$info = make_group_form_components($id);

$delete ="DELETE from groupproperties where '$name'=property and '$id'=groupinstanceid";
$result = mysql_query ($delete);
$err = mysql_error();
if ($err!='') 	group_error ($info, "No such property - $err"); //does not return

$html = <<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
      <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Group Maintenance for Group $id"/>
        <meta name="robots" content="all"/>
        <title>MedCommons Group Maintenance</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "groups.css"; </style>
    </head>
                <table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="Remove Group Property" /></a>
                </td><td>Remove Group Property<small> 
                &nbsp;
					<a href=modGroups.php?id=$id>admin</a>&nbsp;
									acct $accid $email
					</small></td></tr>
					</table>
$info->header
<p>$err</p>
<form action=delgroupproperty.php method=post>
<input type=hidden name=id value='$id'>
<input type=submit value='Ok'>
</form>
</body>
</html>
XXX;

echo $html;
exit;
?>