<?php
require "dbparams.inc.php";
// wld 2/17/06 - added tracking numbers to ccrlog so as to support PINS properly
// wld 6/20/06 - including session.php
// ss  11/7/06 - changed to correctly resolve gateway based on accid and access permissions 
//             - added support for "dest" parameter
// swd 3/22/07 - This is a hacked version of the gwredirguid.php. There are a few problems with it.
// - Doesn't check the rights table.
// - Puts up a window that just needs to be closed in the case of the /PersonalBackup. The browser download window
//   shows the status.
require_once "session.inc.php";
require_once "securelib.inc.php";

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
	
	$mode=r('mode');
	$context=r('context');
	$dest=r('dest');
	
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $auth="";
	
	if (isset($_COOKIE['mc']))
	{
	    $c1 = $_COOKIE['mc'];
		$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
		$props = explode(',',$c1);
		for ($i=0; $i<count($props); $i++) {
			list($prop,$val)= explode('=',$props[$i]);
			switch($prop) {
				case 'mcid': $accid=$val; break;
				case 'fn': $fn = $val; break;
				case 'ln': $ln = $val; break;
				case 'email'; $email = $val; break;
				case 'from'; $idp = stripslashes($val); break;
				case 'auth'; $auth = $val; break;
			}
		}
	}
	if ($accid=='') $accid='0000000000000000';
	
	if ($idp=='') $idp='pops';
	// Should throw an error message if it's pops or if the account is undefined.
	
	
  //  This grabs the node(s) where the specified accid has documents. 
	//$query = "select * from node";
	$query = "SELECT * FROM node WHERE node.node_id= ( select distinct document_location.node_node_id from document, document_location where document.id=document_location.document_id and document.storage_account_id =$accid)";
	//echo "<pre>$query</pre>";
	                 
	$result = xexec($query, "Unable to query node table -");
	
	if(mysql_num_rows($result)==0) {
		return 1;
	}
	
	$node = mysql_fetch_object($result);
	$gw = $node->hostname;
	                 
	setcookie("mcgw", $gw, time()+3600,'/','.'.$GLOBALS['Script_Domain']);
	$url = "$gw/$dest?storageId=$accid";
	
	$terryUrl = $url;
	//$terryUrl = strong_url($url);
	$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway from gwredirguid via $url</title>
<meta http-equiv="REFRESH" content="0;url='$terryUrl'"></HEAD>
<body >
<p>
Please wait while your records are downloaded. They will appear in a zip file in your web browser download folder.
</p>
<p>
	You can close this window at any time.
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
	case 1:  $err="The specified account could not be located for the given user."; break;
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
