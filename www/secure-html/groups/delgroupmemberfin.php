<?php

//remove group admin
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = $_REQUEST['id']; //specifies the group
confirm_admin_access($accid,$id); // does not return if this user is not a group admin

$mcidemail = $_REQUEST['mcid'];
$comment = $_REQUEST['comment'];
$userinfo = lookup_user($mcidemail);
if ($userinfo['mcid']=='') {
	$mcid = "internal error";
	$last = "please contact medcommons support";
	$email = $mcidemail;
	$mobile= "id=$id";
	$status = "Error Processing Remove from Group";
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
	$found = group_del_member($id, $mcid);
	if ($found ) $status = "This user has been removed from the group"; else
	$status = "This user is not a member of the group";
}


$info = make_group_form_components($id);
$desc = "MedCommons Remove Group Member";
$title = 'MedCommons Remove Group Member';
$startpage ="";
$top = make_group_page_top ($info,$accid,$email,$id,$desc,$title,$startpage);
$middle = <<<XXX
<form action=delgroupmember.php method=post>
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
XXX;

$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;

?>
