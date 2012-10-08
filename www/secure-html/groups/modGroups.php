<?php


// show all the group stuff
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
if (isset($_REQUEST['id']))
$id = $_REQUEST['id']; //specifies the group
else $id='';
confirm_admin_access($accid,$id); // does not return if this user is not a group admin

$info = make_group_form_components($id);
$desc = "MedCommons Group Modify";
$title = 'MedCommons Group Manintenance';
$startpage ="groups/modGroups.php?id=$id";
$top = make_group_page_top ($info,$accid,$email,$id,$desc,$title,'');
require_once "dumpgroups.inc.php";

$middle=  dump_groups($accid,$id);

$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;
?>
