<?php

//remove group admin
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = $_REQUEST['id']; //specifies the group
confirm_admin_access($accid,$id); // does not return if this user is not a group admin
$mcidemail = $_REQUEST['mcid'];


$info = make_group_form_components($id);
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

$desc = "MedCommons Remove Group Administrator";
$title = 'MedCommons Remove Group Administrator';
$startpage ="";
$top = make_group_page_top ($info,$accid,$email,$id,$desc,$title,$startpage);
$middle = <<<XXX
<form action=delgroupadminfin.php method=post>
<b>Remove Group Admin</b>
<p>Please confirm admin details
<table class=trackertable>
<input type=hidden name=id value='$id'>
<input type=hidden name=mcid value='$mcidemail'>
<tr><td>MedCommons ID</td><td>$mcid</td></tr>
<tr><td>MedCommons ID</td><td>$mcid</td></tr>
<tr><td>Email</td><td>$email</td></tr>
<tr><td>Name</td><td>$first $middle $last</td></tr>
<tr><td>Mobile</td><td>$mobile</td></tr>
<tr><td>RoleHack</td><td>$role</td></tr>
</table>
<input type=submit value='Confirm Remove Group Admin'>
</form>
XXX;

$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;

?>
