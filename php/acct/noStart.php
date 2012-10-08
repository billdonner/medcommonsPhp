<?php
require_once "alib.inc.php";

require_once "layout.inc.php";
list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database

$message = <<<xxx
<h4>No start page has been set for this user</h4>
<p>you can complain to your administrator, or, 
you can follow <a href=myPrefs.php>this link</a> and fix it yourself</p>
xxx;

echo std("Unassigned Start Page","Unassigned Start Page for $accid",false,
false, stdlayout ( $message ));

?>
