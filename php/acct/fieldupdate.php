<?php
require_once "alib.inc.php";

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0


// set arbitrary db field to specific value
//    table|field|accid|id
//
//if accid <>'' then where accid=current accid id will be added to update filter
//if id <>'' then where id=idval will be added
//
// the value to set comes across in the content field
$type = $_GET['type']; // the field type, its just turned around
$rules = urldecode($_GET['fieldname']);
$incomingvalue  = stripslashes(urldecode($_GET['content']));

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo

$db = aconnect_db(); // connect to the right database

$log = "insert into log set content = 'rules: $rules incoming $incomingvalue' , time=NOW()";
mysql_query($log) or die ("cannot update log rules $log ".mysql_error());
list ($table,$dbfield,$isaccid,$usethisid) = explode('|',$rules); // decode rules

if ((($isaccid=='p')|| ($isaccid=='t'))&& ($type=='checkboxfield'))
{	// for the moment, if this is a checkbox, just read the field from the database and toggle //** need simon help
	if ($isaccid=='p')
	$q="select $dbfield from $table where accid='$accid' and personanum='$usethisid'";
	else
	$q="select $dbfield from $table where id='$usethisid'";
	$result = mysql_query($q) or die ("cant select $q ".mysql_error());
	$row = mysql_fetch_row($result);
	$incomingvalue = 1-$row[0];
	mysql_free_result($result);
}
$outgoingvalue = $incomingvalue;// unless reset, we will display what we got

$where = ''; // build where clause basedonrules
if ($isaccid=='m') $where.=" mcid='$accid' "; else
if (($isaccid=='a')||($isaccid=='p')) $where.=" accid='$accid' "; // wld else


if ($usethisid !='') {
	if ($where!='')  $where.=" and ";
	if ($isaccid=='p')$where.=" personanum='$usethisid' "; else
	$where.=" id='$usethisid' ";
}
if ($where!='')
$where = "where ".$where;

// this is the update we came here to do
$q = "update $table set $dbfield = '$incomingvalue' $where";
mysql_query($q);
$e = mysql_error();
if ($e=='')$e="successfully completed";
else
{
	$e = "could not update table $table field $dbfield";
	echo "$e ".mysql_error();

	$log = "insert into log set content = 'query:$q status: $e' , time=NOW()";
	mysql_query($log) or die ("cannot update log rules $log ".mysql_error());
	//echo $log;
	exit; // do not send anything back if indeed the update failed
}

$synch = time();

$emit = "<ajblock><rtagtype>$type</rtagtype><rfid>$rules</rfid><rcontent>$outgoingvalue</rcontent></ajblock>";

$log = "insert into log set content = 'returned: $emit', time=NOW()";
mysql_query($log) or die ("cannot update log returned ".mysql_error());

echo $emit;
?>