<?php
require_once "dbparamsidentity.inc.php";


function prettyprice ($price)
{
	$dollars = intval($price/100);
	$cents = $price - $dollars*100;
	$tens = intval($cents/10);
	$ones = $cents -$tens*10;
//	echo "Price $price dollars $dollars cents $cents tens $tens ones $ones";
	$vprice = "$".$dollars.".".$tens.$ones;
	return $vprice;
}




/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function enumerate_appservices ($accid){
	


	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	/*  $query = "SELECT id,accid,doctype,idp,guid,status,DATE_FORMAT(date, '%c/%d/%Y %H:%i') as date,src,dest,subject,einfo,tracking,d.dt_privacy_level as eccr_privacy from ccrlog
	left join document_type d on dt_account_id = accid  and (dt_type = 'Emergency CCR') and (dt_tracking_number = tracking)
	where (accid = '$accid') and (status <> 'DELETED') $idpclause;";
	*/

	$query = "SELECT *, a.appserviceid as appid , c.time from appservices a LEFT JOIN appservicecontracts c
	ON a.appserviceid = c.appserviceid WHERE c.accid = '$accid' ";

	$result = mysql_query ($query) or die("can not query table appservices - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$first = true;
	$aid[]=''; // always have something so in_array doesn't fail
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$name=$a['name']; $publisher=$a['publisher']; $description=$a['description']; $serviceurl=$a['serviceurl'];
			$appserviceid = $a['appid'];$removertn = $a['removeurl']; $viewrtn = $a['viewurl'];
			$time = $a['time'];
			$aid[]=$appserviceid; //get a list made
			if ($first == true) 	$out = "<h3>Already in your account:</h3><p><table class='trackertable'>
                <tr><th>service</th><th>publisher</th><th>description</th><th> </th><th> </th></tr>";
			//if ($viewrtn!='')$name="<a target='_new' href='$viewrtn'>$name</a>";
			$view="<a href=appview.php?i=0&a=$name >info</a>";
			$delete = "<a href='appdel.php?s=$appserviceid&r=$removertn'>remove</a>";
			$out.="<tr><td>$name</td><td>$publisher</td><td>$description</td><td>$view</td><td>$delete</td></tr>";
			$first = false;
		};
		$out.="</table></p>";
	} else $out = '';
	mysql_free_result($result);

	$first = true;
	$query = "SELECT * from appservices where builtin <> 'true'";

	$result = mysql_query ($query) or die("can not query table appservices - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount != 0) {
		
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$name=$a['name']; $publisher=$a['publisher']; $description=$a['description']; $serviceurl=$a['serviceurl'];
			$appserviceid = $a['appserviceid']; $initrtn = $a['createurl'];
			if (!in_array($appserviceid,$aid))
			{
				if ($first == true) 	$out .= "<h3>Extensions you can add to your account:</h3><p><table class='trackertable'>
                <tr><th>service</th><th>publisher</th><th>description</th><th> </th><th> </th></tr>";
				$install=  "<a href='purchaseConsent.php?s=$appserviceid&i=$initrtn'>add</a>";
				$view="<a href=appview.php?i=1&a=$name` >info</a>";
				$out.="<tr><td>$name</td><td>$publisher</td><td>$description</td><td>$view</td><td>$install</td></tr>";
				$first = false;
			}
			
		}; 
		$out .= "</table></p>";
	}
	mysql_free_result($result);



	return $out;
}
/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function my_appservices ($accid){

	$out="<small><a href=appservices.php>add</a></small><table class='trackertable'>
                <tr><th>service</th><th>publisher</th><th>description</th><th> </th><th> </th></tr>";

	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	/*  $query = "SELECT id,accid,doctype,idp,guid,status,DATE_FORMAT(date, '%c/%d/%Y %H:%i') as date,src,dest,subject,einfo,tracking,d.dt_privacy_level as eccr_privacy from ccrlog
	left join document_type d on dt_account_id = accid  and (dt_type = 'Emergency CCR') and (dt_tracking_number = tracking)
	where (accid = '$accid') and (status <> 'DELETED') $idpclause;";
	*/

	$query = "SELECT *, a.appserviceid as appid , c.time from appservices a LEFT JOIN appservicecontracts c
	ON a.appserviceid = c.appserviceid WHERE c.accid = '$accid' ";

	$result = mysql_query ($query) or die("can not query table appservices - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$aid[]=''; // always have something so in_array doesn't fail
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$name=$a['name']; $publisher=$a['publisher']; $description=$a['description']; $serviceurl=$a['serviceurl'];
			$appserviceid = $a['appid'];$removertn = $a['removeurl']; $viewrtn = $a['viewurl'];
			$aid[]=$appserviceid; //get a list made
			if ($viewrtn!='')$name="<a target='_new' href='$viewrtn'>$name</a>";
			$view="<a href=appview.php?i=0&a=$serviceurl >info</a>";
			$delete = "<a href='appdel.php?s=$appserviceid&r=$removertn'>remove</a>";
			$out.="<tr><td>$name</td><td>$publisher</td><td>$description</td><td>$view</td><td>$delete</td></tr>";
		};
		$out.="</table></p>";
	}else $out = '';
	mysql_free_result($result);

	

	return $out;
}


function error_exit ($s)
{
	$x=<<<XXX
	<html><head><title>MedCommons Extensions Dependency Constraint</title>
	        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "appsrv.css"; </style>
        </head>
	<body>
	<table><tr><td><a href="index.html" ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="App Services Extensions Constraints" /></a>
             	&nbsp;
                </td></tr>
                </table><p><h4>$s</h4>
                <p>
                <form action=appservices.php method=post>
                <input type=submit value='Ok'>
                </form>
                </body></html>
XXX;
	echo $x;
	exit;
}

function svccontract ($accid,$id)
{ $q="Select 1 from appservicecontracts where '$accid'=accid and  '$id'=appserviceid";

$result = mysql_query ($q) or die("can't query appservicecontracts".mysql_error());
$count = mysql_numrows($result);
return ($count>0);
}

function check_add_dependencies($accid, $appserviceid)
{
	// see if this service has any dependencies which are not yet loaded - yoo hard with join, just do it

	$q = "SELECT *
	             from appservicedependencies 
 				where  (appserviceid = '$appserviceid')";
	$result = mysql_query ($q) or die("can't query appservice dependencies $q ".mysql_error());
	$anyproblemsfound = false;
	while (true) {
		$l=mysql_fetch_assoc($result);
		if ($l===false) return;

		$appsid = $l['dependson'];
		// see if we have a service contract
		if (!svccontract($accid,$appsid)) {
			$anyproblemsfound = true;
			error_exit( "Cant load - please load ".look($appsid)." first<br>");
		}
	}
	if ($anyproblemsfound==false) return;
	exit; // if dependencies, dont return
}

function appservicecontract($accid,$appserviceid)
{		$now=time();
$insert="REPLACE INTO appservicecontracts( accid, appserviceid ,time) ".
"VALUES('$accid','$appserviceid','$now')";
$result = mysql_query ($insert) or
die("can not insert into table appservicecontracts - $insert ".mysql_error());
return mysql_affected_rows();
}
function billingclass($accid){
	$q = "SELECT chargeclass from users where mcid='$accid'";
	$result = mysql_query ($q) or die("can't query users $q ".mysql_error());
	$l=mysql_fetch_array($result);
	mysql_free_result($result);
	return $l[0];
}
function look ($id)
{ 
$q="Select * from appservices where '$id'=appserviceid";
$result = mysql_query ($q) or die("can't query appservices".mysql_error());
$l=mysql_fetch_assoc($result);
if ($l===false) return '';
return $l['name'];
}
function addAppEvent($accid,$appserviceid,$name,$param1)
{
//	if (!isset($GLOBALS['__dbdb']))
	{ // only once
	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	mysql_select_db($db) or die ("can not connect to database $db");
	$GLOBALS['__dbdb']=$db;
	}
	$timenow=time();
	$chargeclass = billingclass($accid);
	$insert="INSERT INTO appeventlog(accid, appserviceid, eventname, param1, time,chargeclass)
				VALUES('$accid','$appserviceid','$name','$param1', '$timenow','$chargeclass')";
	$result = mysql_query ($insert) or
	die("can not insert into table appeventlog - $insert ".mysql_error());
	return true;
}
?>
