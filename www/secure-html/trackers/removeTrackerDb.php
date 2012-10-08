<?php
//
// make trackers db,just make sure we are logged on, then poke the filename back in

require_once 'dblocation.inc.php';
$r=$_REQUEST['r'];
$accid = $_REQUEST['accid']; // must get this for continuity

$dbname = make_tracker_db_name($accid);
unlink($dbname);
setTrackerDb(''); // remove from tables
header("Location: $r?accid=$accid");
exit;

?> 
