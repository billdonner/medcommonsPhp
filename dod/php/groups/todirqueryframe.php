<?php
//todir functions restricted to working just for group with this ID

require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
$id = $_REQUEST['id'];
confirm_admin_access($accid,$id); // does not return if this user is not a group admin
$info = make_group_form_components($id);$html = <<<XXX
<html> <head>
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
<body>
<table><tr><td><a href="index.html" ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="ToDir Query Page" /></a>
                </td><td>ToDir Query Page <small><i>for internal use only</i>
                                 &nbsp;<a TARGET="_parent" href = 'todirquery.php?id=$id>query</a>
&nbsp;<a TARGET="_parent" href = 'todiradd.php?id=$id'>add</a> &nbsp;<a TARGET="_parent" href = 'todirdel.php?id=$id'>delete</a>
acct $accid $email</small></td>
</tr></table>
$info->header
<form method="POST" name="myform" action=todirqueryresults.php target="__queryresults">
<input type=hidden value='$id' name=ctx>

<table>
<tr><td>External ID</td><td><input type=text name=xid></td></tr>
<tr><td>Alias</td><td><input type=text name=alias></td></tr>
<tr><td>MedCommons ID</td><td><input type=text name=accid></td></tr>
</table>
<fieldset>
		<legend>display parameters</legend>
<table><tr><td>max results</td><td><input type=text name=limit value=20></td></tr>
</table>
</fieldset>

<input type=submit name="submit" value="submit">
</form>
</body></html>
XXX;
echo $html;
?>
