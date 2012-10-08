<?php

require_once "alib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not lo
// this is all wired now so that separate fields are updated independently

$db=aconnect_db();
$persona = $_POST['persona'];


	$update = "Update users set persona = '$persona'  where mcid='$accid'";
//	echo $update;
	mysql_query($update) or die ("Can not update persona  - $update".mysql_error());
$GLOBALS['__refreshtop'] = true;
require_once "personainfo.php"
?>