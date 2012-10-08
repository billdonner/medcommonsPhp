<?php
require_once "dbparamsidentity.inc.php";

// clone content (or carbon copy) for MedCommons
//
// clones account contents by cloning ccr log entries - no actual files are copied
//
// clones either whole account, or just the emergency ccr


function doclone ($src,$dst,$red){
	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	// verify src and destination
	$query = "SELECT * from users where (mcid = '$src')";
	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) die ("Source account is invalid in $query");
	$query = "SELECT * from users where (mcid = '$dst')";
	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) die ("Destination account is invalid in $query");
	$a = mysql_fetch_array($result,MYSQL_ASSOC);
	// now get all the rows
	$query = "SELECT * from ccrlog where (accid = '$src')";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) die("Source has no CCRs");
	while ($l = mysql_fetch_array($result,MYSQL_ASSOC)){
		$date = $l['date'];
		$idp = $l['idp'];
		$from = $l['src'];
		$to= $l['dest'];
		$subject = $l['subject'];
		$guid = $l['guid'];
		$status = $l['status'];
		$tracking = $l['tracking'];
		if ($status=='RED') $rowclass = "class='emergencyccr'"; else $rowclass='';
		// clone it by changing just the accid

		$insert="INSERT INTO ccrlog(accid, guid,tracking,status, date ,src, dest,subject,idp) ".
		"VALUES('$dst','$guid','$tracking','$status', NOW(),'$from','$to','$subject','$idp')";

		mysql_query($insert) or die("can not insert into table ccrlog - $insert");

		echo $guid." ".$rowclass."<br>";
	}
	mysql_free_result($result);

	//errcount>0
	mysql_close();
}
// params
$src = $_REQUEST['src'];
$dst = $_REQUEST['dst'];
$red = ($_REQUEST['red']!='');
echo "Cloning ".($red?"emergency ccr":"all docs")." from $src to $dst: ";
doclone($src,$dst,$red);
?>