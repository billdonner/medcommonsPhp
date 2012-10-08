<?php
// show all the group stuff
require_once "../groups/glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
$id = 0;// $_REQUEST['id']; //specifies the group
confirm_admin_access($accid,$id); // does not return if this user is not a group admin

$info = make_group_form_components($id);
$desc = "MedCommons Customer Support Group Administation Page";
$title = 'MedCommons Customer Support Group Administation Page';

$top = make_group_page_top ($info,$accid,$email,$id,$desc,$title,'');
$middle = <<<XXX
<div>
<p>The following functions are available to you as MedCommons Customer Support Group Administrator</p>
<ul>
<li><a href='https://virtual03.medcommons.net/phpMyAdmin/'>Database Administration</a></li>
<li><a href='http://gateway001.private.medcommons.net:9090/router/log.do'>Gateway Administration</a></li>
<li><a href='../groups/showGroups.php'>Show All Groups on MedCommons</a></li>
<li><a href='../groups/modGroups.php?id=$id'>Add Or Remove Members from this Support Group</a></li>
</ul>
</div>
XXX;
$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;
?>
