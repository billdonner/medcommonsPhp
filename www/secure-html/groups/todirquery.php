<?php
//todir functions restricted to working just for group with this ID
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
$id = $_REQUEST['id'];
confirm_admin_access($accid,$id); // does not return if this user is not a group admin
$info = make_group_form_components($id);
$html = <<<XXX
<html>
<frameset rows="300,*">
<frame src="todirqueryframe.php?id=$id">
<frame src="todirqueryresults.php?id=$id" 
name="__queryresults">
</frameset>
</html>
XXX;
echo $html;
?>
