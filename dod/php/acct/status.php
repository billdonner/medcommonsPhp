<?php
require "dbparamsidentity.inc.php";
// takes guid from guid field, uses viewGuid

function dbconnect()
{
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");

	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die("can not connect to database $db");
}


$id = $_REQUEST['id'];
dbconnect();

	$query="SELECT COUNT(*) FROM users";
	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_NUM);
	$users = $a[0];	
	
	$query="SELECT COUNT(*) FROM external_users";
	$result = mysql_query ($query) or die("can not query table external_users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_NUM);
	$xusers = $a[0];
	
	$query="SELECT COUNT(*) FROM ccrlog";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_NUM);
	$ccrlogentries = $a[0];
	
	$query="SELECT COUNT(*) FROM ccrlog WHERE (status<>'DELETED')";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_NUM);
	$deletedentries = $a[0];
	$time = date("H:i:s");                         
echo "<div id='$id'>";
echo "<p>".$GLOBALS['Banner']."</p>";
echo "<p>$time /acct/ at ".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]." ".
                $_SERVER["SERVER_NAME"]."</p>";
                
echo "<p>".$_SERVER["SERVER_SOFTWARE"]." ".$_SERVER["SERVER_PROTOCOL"]."</p>";
echo "<p>db: ".$GLOBALS['DB_Connection']."-". $GLOBALS['DB_Database']."</p>";

echo "<p>Users: ".$users." Liberty Users: ".$xusers." Entries: ".$ccrlogentries." Deleted Entries: ".$deletedentries."</p>";

echo "</div>";
// test7 
?>
