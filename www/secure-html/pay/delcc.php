<?PHP
// handles responses from delete of credit card 
require_once "dbparamspay.inc.php";

$accid = $_REQUEST['accid'];
$cc = $_REQUEST['cc'];
$price = $_REQUEST['price'];



	mysql_connect($GLOBALS['DB_Connection'],
			$GLOBALS['DB_User'],
			$GLOBALS['DB_Password']
			) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	 	 
    // now write an entry in the mysql database

	$delete="DELETE from ccdata where (accid='$accid') and (nikname='$cc')";
	
	mysql_query($delete) or die("can not delete from table ccdata - ".mysql_error());
    mysql_close();
	
// if we get this far, just redirect back to the purchase page

header ("Location: payviacc.php?price=$price");



?>
