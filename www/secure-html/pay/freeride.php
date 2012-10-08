<?PHP 
// this is where flow continues when we give the user a 'free ride' on payment
require_once "dbparamspay.inc.php";

$type = $_POST['TYPE'];
$authcode = $_POST ['AUTHCODE'];
$avsdata = $_POST ['AVSDATA'];
$hostcode = $_POST ['HOSTCODE'];
$pnref= $_POST['PNREF'];
$respmsg = $_POST ['RESPMSG'];
$result = $_POST['RESULT'];
$cscmatch = $_POST['CSCMATCH'];
$custid = $_POST['CUSTID'];
$amount = $_POST['AMOUNT'];
$user1 = $_POST['USER1'];
$user2 = $_POST['USER2'];
$user3 = $_POST['USER3'];
$user4 = $_POST['USER4'];
$user5 = $_POST['USER5'];
$user6 = $_POST['USER6'];
$user7 = $_POST['USER7'];
$user8 = $_POST['USER8'];
$user9 = $_SERVER['PHP_SELF']; //$_POST['USER9'];
$accid = $_REQUEST['USER3'];
$returnurl = $_REQUEST['USER2'];
$vprice = $_REQUEST['USER1'];
$name = $_REQUEST['NAME'];
$address = $_REQUEST['ADDRESS'];
$city = $_REQUEST['CITY'];
$state = $_REQUEST['STATE'];
$zip = $_REQUEST['ZIP'];
$cardnum = $_REQUEST['CARDNUM'];
$name = $_REQUEST['NAME'];

//9147253999 thurs 1015 212 737 3301
mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

// now write an entry in the mysql database

$insert="INSERT INTO ccstatus (time,type,authcode,avsdata,hostcode,pnref,respmsg,csmatch,custid,amount,
		user1,user2,user3,user4,user5,user6,user7,user8,user9) VALUES(".
"NOW(),'$type','$authcode','$avsdata','$hostcode','$pnref','$respmsg','$csmatch','$custid','$vprice',
	           '$accid','$name','$vprice','$address','$city','$state','$zip','$cardnum','$expdate')";

mysql_query($insert) or die("can not insert into table ccstatus - ".mysql_error());

mysql_close();
//amount is filled in above
$amount = 100*$amount; // user unscaled amount

require_once "../acct/appsrvlib.inc.php";
//echo "Add app event paidbill $amount";
$appserviceid = '1234567890';
addAppEvent($accid,$appserviceid,"paidbill",-$amount);
//header("Location: paydone.php");
require_once "paydone.php";
?>


