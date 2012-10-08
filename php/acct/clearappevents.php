<?php
require_once "dbparamsidentity.inc.php";

function verify_logged_in()
{
	$mc = $_COOKIE['mc'];
	if ($mc =='')
	{ header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
	echo "Redirecting to MedCommons Web Site";
	exit;}
	return $mc;
}
// start here
$cl = verify_logged_in();
$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
$c1 = $_COOKIE['mc'];
if ($c1!='')
{
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$props = explode(',',$c1);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;
			case 'fn': $fn = $val; break;
			case 'ln': $ln = $val; break;
			case 'email'; $email = $val; break;
			case 'from'; $idp = stripslashes($val); break;
		}
	}
}
if($GLOBALS['NO_CCRLOG_LOGIN_CHECK'] != true) {
	$mc = verify_logged_in();
}

require_once "alib.inc.php";
require_once "appsrvlib.inc.php";
$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	mysql_select_db($db) or die ("can not connect to database $db");
$del = "DELETE from appeventlog where accid='$accid'";
mysql_query($del) or die("Cant delete entries from appeventlog".mysql_error());
$appserviceid = '1234567890';
addAppEvent($accid,$appserviceid,"cleared",'0');
header ('Location: goStart.php');
echo "Events cleared";

 ?>