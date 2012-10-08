<?php

require_once "alib.inc.php";


list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database

$id = $_REQUEST['id']; // better be there


// find a free slot

$q = "DELETE from personas where accid='$accid' and personanum='$id'";
$result = mysql_query($q) or die("cant delete from personas $q ".mysql_error());

header ('Location: goStart.php');
//echo ("inserted $q");
exit;
?>