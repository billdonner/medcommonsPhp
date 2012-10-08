<?php

require_once "dbparams.inc.php";


//respond to ajax updates on the applianceconfig

$namesx = urldecode($_GET['fieldname']);
   $names = explode('|',$namesx);
	$name = 'ac'.$names[1]; 
$value = urldecode($_GET['content']);

$timenow = time(); // always update the account record to indicate the last time

//
// open database and get account info
$db=$GLOBALS['DB_Database'];

mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");


$ret = "Successfully updated appliance configuration at $timenow";

	$query = "Update mcproperties set value = '$value' where property='$name'";
	$result = mysql_query ($query) or die ("cant $query ".mysql_error());
	if ($result==false)
	{
		$ret= "<p>can not $query - ".mysql_error()."</p>"; break;
	}


	
	// record what we did in "Suggestions"
//	$query = "Insert into suggestions set topic = '$name', refer = '$value', email='$ret'";
//	mysql_query($query);
	
$synch = time();

$out =  "<?xml version='1.0' encoding='UTF-8'?><ajreturnblocks><status>$ret</status><timesynch>$synch</timesynch></ajreturnblocks>";
echo $out;


?>
