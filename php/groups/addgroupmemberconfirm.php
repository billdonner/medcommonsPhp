<?php

//add group admin confirm
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = $_REQUEST['id']; //specifies the group

$info = make_group_form_components($id);
confirm_admin_access($accid,$id); // does not return if this user is not a group admin
$mcidemail = $_REQUEST['mcid'];
$comment = $_REQUEST['comment'];

$userinfo = lookup_user($mcidemail);
if ($userinfo['mcid']=='') {
	$mcid = "notfound";
	$email = $mcidemail;
	$mobile= "id=$id";
	group_error ($info, "Can't find $mcidemail on MedCommons"); //does not return
}
else
{
	$mcid =$userinfo['mcid'];
	$email = $userinfo['email'];
	$first = $userinfo ['first_name'];
	$middle = $userinfo ['middle_name'];
	$last = $userinfo ['last_name'];
	$mobile = $userinfo['mobile'];
	$role = $userinfo['rolehack'];
}


$desc = "MedCommons Add Group Administrator";
$title = 'MedCommons Add Group Administrator';
$startpage ="";
$top = make_group_page_top ($info,$accid,$email,$id,$desc,$title,$startpage);
$middle = <<<XXX


<form action=addgroupmemberfin.php method=post>
<b>Add Group Member</b>
<p>Please confirm member details
<table class=trackertable>
<input type=hidden name=id value='$id'>
<input type=hidden name=mcid value='$mcidemail'>
<input type=hidden name=comment value='$comment'>

<tr><td>MedCommons ID</td><td>$mcid</td></tr>
<tr><td>Email</td><td>$email</td></tr>
<tr><td>Name</td><td>$first $middle $last</td></tr>
<tr><td>Mobile</td><td>$mobile</td></tr>
<tr><td>RoleHack</td><td>$role</td></tr>
</table>
<input type=submit value='Confirm Add Member'>
</form>
XXX;

$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;

?>
