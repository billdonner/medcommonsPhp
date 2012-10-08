<?php 

// handles failure responses from verisign payflo manager
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


	mysql_connect($GLOBALS['DB_Connection'],
			$GLOBALS['DB_User'],
			$GLOBALS['DB_Password']
			) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	 	 
    // now write an entry in the mysql database

	$insert="INSERT INTO ccstatus (time,type,authcode,avsdata,hostcode,pnref,respmsg,csmatch,custid,amount,
		user1,user2,user3,user4,user5,user6,user7,user8,user9) VALUES(".
	    "NOW(),'$type','$authcode','$avsdata','$hostcode','$pnref','$respmsg','$csmatch','$custid','$amount',
	           '$user1','$user2','$user3','$user4','$user5','$user6','$user7','$user8','$user9')";
	
	mysql_query($insert) or die("can not insert into table silentpos - ".mysql_error());
    mysql_close();
	
echo "200 OK\r\n"; // indicate we have received the data


?>