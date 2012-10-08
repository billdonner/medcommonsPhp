<?php

require_once "appsrvlib.inc.php";



// start here
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
//con ed 800 752 6633

$appserviceid = $_POST['s'];
$init = $_POST['i'];

$db=$GLOBALS['DB_Database'];
mysql_pconnect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");

check_add_dependencies ($accid, $appserviceid);
$aff=appservicecontract($accid,$appserviceid);
$billingclass = billingclass($accid);
$timenow=time();
$name = "LoadExtension";
$param1 = "0,1,1,0,0,0,0"; 
$insert="INSERT INTO appeventlog(accid, appserviceid, eventname, param1, time, chargeclass)
				VALUES('$accid','$appserviceid','$name','$param1', '$timenow','$billingclass')";
$result = mysql_query ($insert) or
die("can not insert into table appeventlog - $insert ".mysql_error());

if (($init!='')&&($aff!=2))
header("Location: $init");
else
header ("Location: appservices.php?f=$aff");
echo "Successfully added";


exit;
?>