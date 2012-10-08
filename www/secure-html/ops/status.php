<?php
require "dbparamsmcextio.inc.php";
// mini status report

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


	
	$query="SELECT COUNT(*) FROM emailstatus";
	$result = mysql_query ($query) or die("can not query table emailstatus - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_NUM);
	$counter = $a[0];

	$time = date("H:i:s");                         
echo "<div id='$id'>";
echo "<p>".$GLOBALS['Banner']."</p>";
echo "<p>$time/ops/ at ".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]." ".
                $_SERVER["SERVER_NAME"]."</p>";
                
echo "<p>".$_SERVER["SERVER_SOFTWARE"]." ".$_SERVER["SERVER_PROTOCOL"]."</p>";
echo "<p>db: ".$GLOBALS['DB_Connection']."-". $GLOBALS['DB_Database']."</p>";

echo "<p>Emails: ".$counter."</p>";

echo "</div>";

?>