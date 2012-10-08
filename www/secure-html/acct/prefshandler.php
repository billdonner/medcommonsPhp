<?php

require_once "alib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not lo
// this is all wired now so that separate fields are updated independently

function setfield($f,$v)
{
	//	echo "setfield $f $v<br>";
	$GLOBALS['field']=$f;
	$GLOBALS['value']=$v;

}
function narf ($label)
{
	$updatelabel = 'go'; //must match the value in prefs.inc.php

	if (isset($_REQUEST[$label.'submit'])) if  ($_REQUEST[$label.'submit']==$updatelabel)
	{
		if (isset($_REQUEST[$label]))
		$x = $_REQUEST[$label]; else $x = false;
		if ($x !==false) setfield($label,$x);
	}
}

$db=aconnect_db();
$GLOBALS['field']='';
narf('picslayout');
narf('persona');
narf('chargeclass');
narf('photoUrl');
narf('affiliationgroupid');
narf('stylesheeturl');
narf('rolehack');
//echo $GLOBALS['field'];
if($GLOBALS['field']!='') {
	$update = "Update users set ".$GLOBALS['field']."='".$GLOBALS['value']."' where mcid='$accid'";
	//echo $update;
	mysql_query($update) or die ("Can not update users  - $update".mysql_error());
}
require_once "goStart.php";
//echo "Account Preferences Modified via $update";
?>