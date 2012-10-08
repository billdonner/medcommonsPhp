<?php
require_once "session.inc.php";

// turns a tracking number into a Guid, and ultimately redirects through tracking.jsp
function tracking_process($tracking)
{	// if the tracking number is found, the user is redirected to the correct gateway
	dbconnect();
	$gatewayurl = tracking_to_node_guid($tracking,$guid);
	if ($gatewayurl != false) tracking_redirect($gatewayurl,$tracking); //*******
	return;
}
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

function tracking_to_node_guid ($t,&$guid)
{ // converts a tracking number into a gateway node to redirect to:
// returns URL, or FALSE

$select ="SELECT * FROM tracking_number
					WHERE (tracking_number='$t')";

$result = xexec($select,"can not select from table tracking_number - ");
$count = mysql_numrows($result);
$trobj = mysql_fetch_object($result);
//echo $trobj;
if ($trobj === false) return false;

$rights_id = $trobj->rights_id;

// go to the rights table to get the document id
$select="SELECT * FROM rights WHERE (rights_id = '$rights_id')";
$result = xexec($select,"can not select from table rights - ");
$robj = mysql_fetch_object($result);
//echo $robj;
if ($robj===FALSE) return false;

$docid =$robj->document_id;

// go to the document table to get the guid
$select="SELECT * FROM document WHERE (id = '$docid')";
$result = xexec($select,"can not select from table document - ");
$dobj = mysql_fetch_object($result);
//echo $dobj;
if ($dobj===FALSE) return false;
$guid = $dobj->guid;
// go to the document location table to get a nodeid

$select="SELECT * FROM document_location WHERE (document_id = '$docid')";
$result = xexec($select,"can not select from table document_location - ");
$dlobj = mysql_fetch_object($result);
//echo $dlobj;
if ($dlobj===FALSE) return false;

$nodeid = $dlobj->node_node_id;

// go to the node table to get hostname, keys, etc
$select="SELECT * FROM node WHERE (node_id = '$nodeid')";
$result = xexec($select,"can not select from table node - ");
$nobj = mysql_fetch_object($result);
//echo $nobj;
if ($nobj===FALSE) return false;
//echo $nobj->hostname;
//
//if we are going to return ok, then hack in an update to the ccrlog table if we are logged on
//

return $nobj->hostname; //hooray
}

function tracking_redirect($gw,$tracking) //*****
{	
$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
$c1 = $_COOKIE['mc'];
if ($c1!='')
{

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
//******* 	

$url = "$gw/tracking.jsp?tracking=$tracking&accid=$accid&idp=$idp";
$terryUrl = strong_url($url);
$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway from track.inc.php via $url</title>
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
?>
