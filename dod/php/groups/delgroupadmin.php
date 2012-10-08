<?php

//remove group admin
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = $_REQUEST['id']; //specifies the group
confirm_admin_access($accid,$id); // does not return if this user is not a group admin

$info = make_group_form_components($id);
$desc = "MedCommons Remove Group Administrator";
$title = 'MedCommons Remove Group Administrator';
$startpage ="";
$top = make_group_page_top ($info,$accid,$email,$id,$desc,$title,$startpage);
$middle = <<<XXX

<form action=delgroupadminconfirm.php method=post>
<b>Remove Group Admin</b>
<table class=trackertable>
<input type=hidden name=id value='$id'>
<tr><td>MedCommons ID or Email</td><td><input name=mcid size=60>
</td>
</tr>

</table>
<input type=submit value='Remove Administrator'>
</form>
XXX;

$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;

?>
