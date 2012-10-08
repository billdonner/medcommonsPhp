<?php

// produce a table with all the user info MedCommons has stored about a user with specified account id

require_once "alib.inc.php";
require_once "prefs.inc.php";
require_once "layout.inc.php";


list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database
if (isset($_REQUEST['valid']))
$valid = $_REQUEST['valid']; else $valid="abcdefghijklmnopqrstuvwxyz";



echo std("My MedCommons Preferences Page","My MedCommons Preferences Page for $accid",false,
false, stdlayout (  set_prefs ($accid,$valid)));


?>