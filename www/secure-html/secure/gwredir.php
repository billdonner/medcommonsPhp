<?php
//  handling for ?a opcode args - all go to tracking.jsp
require "dbparams.inc.php";
require "session.inc.php";

// redirect by guid
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
// start here

dbconnect();

$op=$_REQUEST['a']; // get operation code

// if logged on, pass that infor along

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

$page="tracking.jsp";
$extraparams="";

// fix opcode to conform to simon's implementation
switch ($op) {
	case 'CreateCCR' : {$op = 'new'; if ($email!='') $op.="&from=$email";break;}
	case 'ImportCCR' : {$op = 'import'; break;}
	case 'OpenCCR':    {$op = 'saved'; break;}
	case 'AddDocument':{$op = 'addDocument'; $page="accountDocument.jsp"; $extraparams="&returnUrl=".urlencode($GLOBALS['Accounts_Url'])."/addDocument.php"; break;}
  default:	{
    $op =$_REQUEST['tracking'];
    $pin = "&p=".$_REQUEST['pin'];
    break;
  }
}

if($GLOBALS['CreateCCRNodeURL']) {
  $gw = $GLOBALS['CreateCCRNodeURL'];
}
else {
  // find the gateway we should be creating things on by looking at the propertices 
  // go to the document table to get the guid
  $select="SELECT * FROM mcproperties WHERE (property = 'CreateCCRNodeID')";
  $result = xexec($select,"can not select CreateCCRNodeID from  table mcproperties - ");
  $dobj = mysql_fetch_object($result);
  if ($dobj===FALSE) return false;
  $nodeid = $dobj -> value;

  // go to the node table to get hostname, keys, etc - just get the 1st
  $select="SELECT * FROM node WHERE (node_id = '$nodeid')";
  $result = xexec($select,"can not select from table node - ");

  $nobj = mysql_fetch_object($result);
  if ($nobj===FALSE) return false;
  // got the node, go there
  $gw = $nobj->hostname; //hooray
}


$url = "$gw/$page?tracking=$op&accid=$accid&idp=$idp$pin$extraparams";
$terryUrl = strong_url($url);
	$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway Op $op via $url</title>
<meta http-equiv="REFRESH" content="0;url='$terryUrl'"></HEAD>
<body >
<p>
Please wait...
</p>
</body>
</html>
XXX;
	echo $x;

?>
