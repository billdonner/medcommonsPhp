<?php
require "dbparams.inc.php";
// takes guid from guid field, uses viewGuid
// wld 2/17/06 - added tracking numbers to ccrlog so as to support PINS properly
// wld 6/20/06 - including session.php

require_once "session.inc.php";
function dbconnect()
{
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");

	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die("can not connect to database $db");
}

function xexec ($s, $p)
{
	$result = mysql_query($s) or die("Can not query in xexec $p ".mysql_error());
	if ($result=="") {exit;}
	return $result;
}
function r($r)
{
	if (isset($_REQUEST[$r])) return $_REQUEST[$r]; else return '';
}
function try_redirect()
{
// should not exit if successful, otherwise returns with an error code which is turned into a decent error message
// start here
dbconnect();
$qs=$_SERVER["QUERY_STRING"];
$guid=r('guid');
$raw=r('raw');
$tracking=r('tracking');
$free=r('free');
$mode=r('mode');
$context=r('context');

$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";

if (isset($_COOKIE['mc']))
{
    $c1 = $_COOKIE['mc'];
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$props = explode(',',$c1);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;

			case 'fn': $fn = $val; break;

			case 'ln': $ln = $val; break;

			case 'email'; $email = $val; break;

			case 'from'; $idp = stripslashes($val); break;

		}

	}
}
if ($accid=='') $accid='0000000000000000';
if ($idp=='') $idp='pops';
// find the node 
// go to the document table to get the guid
$select="SELECT * FROM document WHERE (guid = '$guid')";
$result = xexec($select,"can not select  by guid from  table document - ");
$dobj = mysql_fetch_object($result);
//echo $dobj;
if ($dobj===FALSE) return 1;
$docid = $dobj -> id;

// go to the document location table to get a nodeid

$select="SELECT * FROM document_location WHERE (document_id = '$docid')";
$result = xexec($select,"can not select from table document_location - ");
$dlobj = mysql_fetch_object($result);
//echo $dlobj;
if ($dlobj===FALSE) return 2;

$nodeid = $dlobj->node_node_id;

// go to the node table to get hostname, keys, etc - just get the 1st
$select="SELECT * FROM node WHERE (node_id = '$nodeid')";
$result = xexec($select,"can not select from table node - ");

$nobj = mysql_fetch_object($result);
if ($nobj===FALSE) return 3;

// got the node, go there
$gw = $nobj->hostname; //hooray

if($raw == 'true') { // raw guid - do not pass viewer, go directly to jail
  $url = "$gw/streamDocument.do?guid=$guid&accid=$accid&idp=$idp";
}
else
if (($tracking=='') || ($free==true))
  $url = "$gw/viewGuid.jsp?guid=$guid&tracking=$tracking&accid=$accid&idp=$idp&mode=$mode&context=$context"; 
else
  $url = "$gw/tracking.jsp?tracking=$tracking&accid=$accid&idp=$idp&context=$context";
$terryUrl = strong_url($url);
$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway from gwredirguid via $url</title>
<meta http-equiv="REFRESH" content="0;url='$terryUrl'"></HEAD>
<body >
<p>
Please wait...
</p>
</body>
</html>
XXX;
echo $x;
exit;
}

// main program

$stat = try_redirect();
switch ($stat) {
	case 1:  $err="There is no document with that guid"; break;
	case 2:  $err="Document exists but no entry in document location table"; break;
	case 3:	 $err="Repository Gateway associate with the document is not found"; break;
	default: $err="Unknown Redirection Error";
}
$x=<<<XXX
<html><head><title>Redirection Error</title>
</head>
<body >
<p>
$err
</p>
<p>
Sorry.... If trouble persists please contact <a href="mailto:cmo@medcommons.net">technical support</a>
</p>
</body>
</html>
XXX;
echo $x;
?>
