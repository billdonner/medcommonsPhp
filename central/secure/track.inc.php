<?php
function tracking_process($tracking)
{	// if the tracking number is found, the user is redirected to the correct gateway
	dbconnect();
	$gatewayurl = tracking_to_node($tracking);
	if ($gatewayurl != false) tracking_redirect($gatewayurl,$tracking);
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

function tracking_to_node ($t)
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

$docid =$robj->document_ID;

// go to the document table to get the guid
$select="SELECT * FROM document WHERE (id = '$docid')";
$result = xexec($select,"can not select from table document - ");
$dobj = mysql_fetch_object($result);
//echo $dobj;
if ($dobj===FALSE) return false;

// go to the document location table to get a nodeid

$select="SELECT * FROM document_location WHERE (document_ID = '$docid')";
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
	$mc = $_COOKIE['mc'];
	if ($mc !='')
	{
    $accid=""; $idp="";
    $from=stripslashes($_REQUEST['from']);
    $to=$_REQUEST['to'];
    $tracking = $_REQUEST['a'];// original tracking number
    $subject = $_REQUEST['subject'];
	$props = explode(',',$mc);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;
			case 'from': $idp=$val; break;

		}
	}

	$insert="INSERT INTO ccrlog(accid, guid ,status, date ,src, dest,subject,samlidp) ".
				"VALUES('$accid','$tracking','OK', NOW(),'$from','$to','$subject','$idp')";
	xexec($insert,"sorry, ".mysql_error());
	}
return $nobj->hostname; //hooray
}

function tracking_redirect($g,$t)
{
	$url = "$g/tracking.jsp?tracking=$t";
	$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway $g</title>
<meta http-equiv="REFRESH" content="0;url='$url'"></HEAD>
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