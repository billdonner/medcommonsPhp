<?php
// practice group admin page
require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
$pid = $_REQUEST['pid']; //specifies the practicegroup
practice_ids ($pid,$providergroupid,$patientgroupid); // get the actual groups

confirm_member_access($accid,$providergroupid); // does not return if this user is not a group admin

$info = make_group_form_components($providergroupid);
$desc = "MedCommons Practice Administration";
$title ="HealthCare Providers Page of $info->groupname";
$startpage='groups/spPracticeProvider.php';
$top = make_group_page_top ($info,$accid,$email,$providergroupid,$desc,$title,$startpage);
$middle = <<<XXX
<div>
<p>The following functions are available to you as a Healthcare Provider of $info->groupname</p>
<ul>
<li><a href='modGroups.php?id=$practicegroupid'>Add or Remove Patients</a></li>
<li><a href='grls/query.php?pid=$pid'>Workflow</a></li>
<li><a >Administer ToDir Entries for This Practice (on ice)</a></li>
</ul>
</div>
XXX;
$bottom = make_group_page_bottom ($info);
echo $top.$middle.$bottom;
?>
