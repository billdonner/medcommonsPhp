<?php 
// ajax server side call to
// send back only those updates needed to bring the screen up to date {
require_once "dbparamsidentity.inc.php";
require_once "ccrloglib.inc.php"; // the hard work is all in here

$lasttime = $_GET['lt']; // get last time ajax client heard from us
$accid = $_GET['accid']; // get accountid
$interval = $_GET['interval']; // get accountid
$mini = $_GET['mini'];
$mini = ($mini==1);
checkupdate ($accid, $lasttime, $doacct, $dotabs);
$stat = '';
if ($doacct==true) $stat.="card ";
if ($dotabs==true) $stat.="tabs ";
$synch = time();
if ($stat=='') {$stat='>';
	$offby = ($synch - $lasttime) -$interval;
	if ($offby>10) $offby=10; 
	for ($i=0; $i<$offby; $i++)
				$stat.='-';
	};
$emit = "<ajblock>";
if (($doacct==true) or ($dotabs==true)){
	// must read the db even if account hasn't changed
// do a bunch of database reads to get rows from ccr log, sorted by idp
$count = readdb($mini,$accid,$from,$content,$tab,$emailbuf,$fn,$ln,$email,$street1,$street2,
$city,$state,$postcode,$country,$mobile,$emergencyccr,$patientcard,$einfo,$trackerdb);
  
$emit .= "<patientcard>$patientcard</patientcard><emergencyccr>$emergencyccr</emergencyccr><einfo>$einfo</einfo>";

}
if ($dotabs==true) {
// put together tab0, must get count and tabs anyway
$tab0content = tab0(true,$email,$mobile,$ln,$fn,$street1,$street2,$city,$state,$postcode);
// assemble all the tabs
$alltabs = assembletabs($mini, $count,$content,$tab,$tab0content);
$emit .= "<content>$alltabs</content>";
}
// echo back the whole div

$emit .="<timesynch>$synch</timesynch><status>$stat</status></ajblock>";


echo $emit;

exit;


function checkupdate ($accid, $lasttime, &$doacct, &$dotabs)
{
	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	$query = "SELECT * from users where (mcid = '$accid')";
	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) { echo "cant find account"; return false;}
	$a = mysql_fetch_array($result,MYSQL_ASSOC);
	$updatetime = $a['updatetime'];
	$ccrlogupdatetime = $a['ccrlogupdatetime'];

	$dotabs = ($ccrlogupdatetime>$lasttime);
	$doacct =  ($updatetime>$lasttime);
	
}

?>
