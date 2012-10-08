<?php 

require_once "dbparamsidentity.inc.php";
/*** goes to the users main account page based upon his current logged on account id ****/

function verify_logged_in()
{
	$mc = $_COOKIE['mc'];
if ($mc =='')
	{ header("Location: ".$GLOBALS['Identity_Base_Url']."/logout");
  		echo "Redirecting to SignOn or Register Page";
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
//echo "mcid is $accid ($idp) $email $fn $ln";
}


$args="accid=$accid&from=$idp";

// new, read the acct record right now to figure out what sort of acct this actually is, and then dispatch to the right place

$db=$GLOBALS['DB_Database'];
mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

$query = "SELECT * FROM users WHERE (mcid = '$accid')";
$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
$rowcount = mysql_num_rows($result);
if ($rowcount == 0) die("inconsistency in users table");
$record = mysql_fetch_array($result,MYSQL_ASSOC);
$redirurl = $record['rolehack'];
if ($redirurl =='') $redirurl="acct/myPage.php";
$redirurl = "../".$redirurl;
$args="accid=$accid&from=$from";
$loc = "$redirurl?$args";
header("Location: $loc");
echo "Redirecting to $loc";
exit();

?>
