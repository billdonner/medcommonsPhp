<?php

require_once "glib.inc.php";
list($accid,$fn,$ln,$myemail,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = $_REQUEST['id'];
confirm_admin_access($accid,$id); // does not return if this user is not a group admin


$mcidemail = $_REQUEST['mcid'];
$comment = $_REQUEST['comment'];
$userinfo = lookup_user($mcidemail);
if ($userinfo['mcid']=='') {
	$mcid = "internal error";
	$last = "please contact medcommons support";
	$email = $mcidemail;
	$mobile= "id=$id";
	$status = "Error Processing Add to Group";
}
else
{
	
	// this is the success path, gather user info and add this user to the members part of this group
	$mcid =$userinfo['mcid'];
	$email = $userinfo['email'];
	$first = $userinfo ['first_name'];
	$middle = $userinfo ['middle_name'];
	$last = $userinfo ['last_name'];
	$mobile = $userinfo['mobile'];
	$role = $userinfo['rolehack'];
	// group_add reports duplicates
	$dupe = group_add_member($id, $mcid, $comment);
	if ($dupe ) $status = "This user is already a member of this group"; else 
	$status = "This user has been added to the group";
}

$info = make_group_form_components($id);

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
        <title>MedCommons Added Member to Group</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "groups.css"; </style>
    </head>
                <table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="Added Member to Group" /></a>
                </td><td>Confirm Add Member<small> 
                &nbsp;
						<a href=modGroups.php?id=$id>admin</a>&nbsp;
										acct $accid $email
					</small></td></tr>
					</table>
$info->header

<form action=addgroupmember.php method=post>
<b>$status</b>
<p>
<table class=trackertable>
<input type=hidden name=id value='$id'>
<tr><td>MedCommons ID</td><td>$mcid</td></tr>
<tr><td>Email</td><td>$email</td></tr>
<tr><td>Name</td><td>$first $middle $last</td></tr>
<tr><td>Mobile</td><td>$mobile</td></tr>
<tr><td>RoleHack</td><td>$role</td></tr>
</table>
<input type=submit value='Ok'>
</form>
</body>
</html>
XXX;

echo $html;
?>