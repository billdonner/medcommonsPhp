<?PHP
// handles responses from add of credit card 
require_once "dbparamspay.inc.php";

$name = $_POST['NAME'];
$address = $_POST['ADDRESS'];
$city = $_POST['CITY'];
$state = $_POST['STATE'];
$zip = $_POST['ZIP'];
$cardnum = $_POST['CARDNUM'];
$expdate = $_POST['EXPDATE'];
$nikname = $_POST['NIKNAME'];
$accid = $_POST['mcid'];
$price = $_POST['price'];

	mysql_pconnect($GLOBALS['DB_Connection'],
			$GLOBALS['DB_User'],
			$GLOBALS['DB_Password']
			) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("addcc can not connect to database $db");
	 	 
    // now write an entry in the mysql database

	$insert="INSERT INTO ccdata (accid,nikname,name,addr,city,state,zip,cardnum,expdate) VALUES(".
	    "'$accid','$nikname','$name','$address','$city','$state','$zip','$cardnum',
	           '$expdate')";
	
	mysql_query($insert) or die("can not insert into table ccdata - ".mysql_error());
    mysql_close();
	
// if we get this far, just redirect back to the purchase page

header ("Location: payviacc.php?price=$price");


?>
