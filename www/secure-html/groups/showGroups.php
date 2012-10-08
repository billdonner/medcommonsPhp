<?php
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
confirm_admin_access($accid,0); // does not return if this user is not a group admin
$info = make_group_form_components(0);
$desc = "MedCommons Show Groups";
$title = 'Show Groups';
$top = make_group_page_top ($info,$accid,$email,0,$desc,$title,'');
// show all the group stuff
require_once "dumpgroups.inc.php";
$middle= dump_groups($accid, -1);
$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;
?>
