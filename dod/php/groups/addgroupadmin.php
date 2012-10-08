<?php

//add group admin
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = $_REQUEST['id']; //specifies the group
confirm_admin_access($accid,$id); // does not return if this user is not a group admin

$info = make_group_form_components($id);
$desc = "MedCommons Add Group Administrator";
$title = 'MedCommons Add Group Administrator';
$startpage ="";
$top = make_group_page_top ($info,$accid,$email,$id,$desc,$title,$startpage);
$middle = <<<XXX

<form action=addgroupadminconfirm.php method=post>
<b>Add Group admin</b>
<table class=trackertable>
<input type=hidden name=id value='$id'>
<tr><td>MedCommons ID or Email</td><td><input name=mcid size=60>
</td>
</tr>
<tr><td>Alias</td><td><input name=comment size=60>
</td>
</tr>
</table>
<input type=submit value='Add admin'>
</form>
XXX;
$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;

?>
