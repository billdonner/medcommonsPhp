<?php



require_once "alib.inc.php";
require_once "userinfo.inc.php";
require_once "layout.inc.php";


list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database


echo std("Personalize","Peronsalize Preferences Page for $accid",false,
false, stdlayout ( userinfo($accid,0)));

?>