<?php

require_once "dbparams.inc.php";

//
// start here
//
$gwnode = $_REQUEST['gwnode'];// posted by once.php
mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");
$f=$GLOBALS['Homepage_Url']."/gwnodes.dat";

echo "loading $f<br>";
$str = file_get_contents($f);

$insert="Delete from node";
mysql_query($insert) or die("can not delete from table node - ".mysql_error());


// cant deal with xml on virt03, use a pipe format
$lines = explode(';',$str);
// separate by pipes within
$lcount = count($lines)-1; // dont count final semicolon
for ($i=0; $i<$lcount; $i++)
{
	// each line is id, name, fixedip
	list($id,$name,$fixedip)=explode (',', $lines[$i]);
	trim($id); trim($name); trim ($fixedip);
	$time = time();
	$insert = "Insert into node set node_id='$id', hostname='$name', fixed_ip='$fixedip', node_type='0'";
	mysql_query($insert) or die("can not insert table node $insert- ".mysql_error());
	if ($id==$gwnode) echo "setting $name as default gateway for new CCRs and documents<br>";
}


// now adjust the free

$insert="Update  mcproperties set value='$gwnode' where property='CreateCCRNodeID'";
mysql_query($insert) or die("can not update table mcproperties - ".mysql_error());

//header("Location: ".$GLOBALS['Homepage_Url']);
echo "your medcommons database is ready<br>";
echo "medcommons <a href=".$GLOBALS['Homepage_Url'].">homepage</a>";
?>