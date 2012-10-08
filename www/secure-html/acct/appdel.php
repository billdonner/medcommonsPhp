<?php

require_once "appsrvlib.inc.php";




function check_contrary_dependencies($accid, $appserviceid)
{
	// see if this service has any dependencies which are not yet loaded - yoo hard with join, just do it

	$q = "SELECT *
	             from appservicedependencies 
 				where  (dependson = '$appserviceid')";
	$result = mysql_query ($q) or die("can't query appservice dependencies $q ".mysql_error());
	$anyproblemsfound = false;
	while (true) {
		$l=mysql_fetch_assoc($result);
		if ($l===false) return;

		$appsid = $l['appserviceid'];
		// see if we have a service contract
		if (svccontract($accid,$appsid)) {
			$anyproblemsfound = true;
			error_exit( "Cant unload due to dependencies - please unload ".look($appsid)." )<br>");
		}
	}
	if ($anyproblemsfound==false) return;
	exit; // if dependencies, dont return
}

function removeappservicecontract($accid,$appserviceid)
{		check_contrary_dependencies($accid,$appserviceid); // doesn't return if no good
$delete="DELETE FROM appservicecontracts WHERE accid='$accid' and appserviceid='$appserviceid'";
$result = mysql_query ($delete) or die("can not delete from table appservicecontracts - $delete ".mysql_error());
}

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

$removertn=$_REQUEST['r'];
$appserviceid = $_REQUEST['s'];

$db=$GLOBALS['DB_Database'];
mysql_pconnect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");

removeappservicecontract($accid,$appserviceid);
$billingclass = billingclass($accid);
$timenow=time();
$name = "RemoveExtension";
$param1 = "0,0,0,0,0,0,0";
$insert="INSERT INTO appeventlog(accid, appserviceid, eventname, param1, time,chargeclass)
				VALUES('$accid','$appserviceid','$name','$param1', '$timenow','$billingclass')";
$result = mysql_query ($insert) or
die("can not insert into table appeventlog - $insert ".mysql_error());

if ($removertn!='' ) header("Location: $removertn?r=".$GLOBALS['Extensions_Url']."appservices.php"); else
header ("Location: appservices.php");
echo "Successfully removed";
exit;
?>