<?php
// practice group admin page
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
$pid = $_REQUEST['pid']; //specifies the practicegroup
practice_ids ($pid,$providergroupid,$patientgroupid); // get the actual groups

confirm_member_access($accid,$patientgroupid); // does not return if this user is not a group member

$info = make_group_form_components($patientgroupid);
$desc = "MedCommons Practice Administration";
$title = "Patient Portal for $info->groupname";
$startpage='groups/spPracticeProvider.php';
$top = make_group_page_top ($info,$accid,$email,$patientgroupid,$desc,$title,$startpage);
$middle = <<<XXX
<div>
<p>The following functions are available to you as a patient $info->groupname</p>
<ul>
<li>coming soon</li>
</ul>
</div>
XXX;
$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;
?>
