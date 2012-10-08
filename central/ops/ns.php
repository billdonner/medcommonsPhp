<?php
require_once  "../../dbparamsmcback.inc.php";
// update certificate db with lastaccess
include("/home/ops/htdocs/ca/php-ca/include/lastaccess.php");

function show_status($p,$q, $kind)
{
$query = "SELECT * from $p";

$result = mysql_query ($query) or die("can not query table $p - ".mysql_error());
$count=0;
if ($result=="") {echo "?no records in $p?"; return;}

echo "<table border=2><tr><th><b>Description<b></th><th><b>$q<b></th><th><b>Status<b></th><th><b>SW Version<b></th><th><b>notes<b></th></tr>";

while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$nickname = $l['nickname'];
	$ctprot = $l['ctprot'];
	$cthost = $l['cthost'];
	$ctport= $l['ctport'];
	$ctfile = $l['ctfile'];
	$description = $l['description'];
	$status = $l['summarystatus'];
	$ss=substr($status,0,2);
	$notes = $l['notes'];
	$ipaddr = $l['ipaddr'];
	$dbconnection = $l['dbconnection'];
	$dbdatabase = $l['dbdatabase'];
	$swversion = $l['swversion'];
	$swrevision = $l['swrevision'];

	$hp = $kind.$cthost;
	if ($kind=='') $hp = $cthost; else {$hp=$kind.$dbdatabase.".".$cthost;};

	if ($ss=="ER") $hpcolor="<FONT COLOR=#ff0000>".$hp."</FONT>"; else $hpcolor = $hp;
	if ($ss=="ER") $ss="<FONT COLOR=#ff0000>".$ss."</FONT>";


	$ct = $ctprot."://".$cthost.":".$ctport.$ctfile;//wld now passed in from database

	$count++;
	$xx=<<<XXX
<tr><td>$description</td>
<td>
<a href="$nickname"
onmouseover="this.T_WIDTH=200;this.T_FONTCOLOR='#003399';return escape('$cthost $ipaddr $dbconnection $dbdatabase')" target='_NEW'>$hpcolor</a>
</td>
<td><a href='$ct' target='_NEW' onmouseover="this.T_WIDTH=200;this.T_FONTCOLOR='#003399';return escape('$status')">$ss</a></td>
		<td>$swversion%$swrevision</td>
		<td>$notes</td>
		</tr>
XXX;
	echo $xx;

}
mysql_free_result($result);
echo "</table><p>";



}
// show status of the network

$db=$GLOBALS['DB_Database'];
$a = $GLOBALS['DB_Connection'];
$r = $GLOBALS['Default_Repository'];
$srvname = $_SERVER['SERVER_NAME'];

$srva = $_SERVER['SERVER_ADDR'];
$srvp = $_SERVER['SERVER_PORT'];
$gmt = gmstrftime("%b %d %Y %H:%M:%S")." GMT";
$uri = htmlspecialchars($_SERVER ['REQUEST_URI']);
$s1=$_SERVER["SSL_CLIENT_S_DN"];
$s2=$_SERVER["SSL_CLIENT_I_DN"];
//main
// get a select list of all gatways
$x=<<<xxx
<html><head><title>MedCommons Network Status Display</title><meta http-equiv="refresh" content="60"></head>
<body>
<table border=0> <tr><td><img src="MEDcommons_logo_246x50.gif" width=246 height=50 alt='medcommons, inc.'></td><td><table border=0>
<tr><td><h4>MedCommons Network Status at $gmt</h4></td></tr>
<tr><td><small>$s1</small></a></td></tr>
<tr><td><small>$s2</small></a></td></tr>
</table></td>
</tr></table><br>
xxx;

mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");
echo $x;// f here, we have a good database

show_status('spprobes','MedCommons Policy Providers','sp.');
show_status('idpprobes','Identity Providers','idp.');
show_status('xioprobes','External IO Interfaces','xio.');

show_status('gatewayprobes','MedCommons Gateways','');//,"/router/status.do?fmt=xml","

mysql_close();
$x=<<<xxx
<p><small>The polling database engine is $db on $srvname ($srva:$srvp)</small>
<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script></body></html>
xxx;
echo $x;
exit;

?>
