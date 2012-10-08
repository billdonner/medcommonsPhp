<?php
require_once "session.inc.php";
require_once "securelib.inc.php";
require_once "db.inc.php";
require_once "utils.inc.php";

// turns a tracking number into a Guid, and ultimately redirects through tracking.jsp
function tracking_process($tracking, $pin = false)
{	// if the tracking number is found, the user is redirected to the correct gateway
	dbconnect();
	$gatewayurl = tracking_to_node_guid($tracking,$guid);
  dbg("got gateway $gatewayurl and guid $guid for lookup of tn $tracking");
	if ($gatewayurl != false) tracking_redirect($gatewayurl,$guid,$tracking,$pin); //*******
	return;
}

function xexec ($s, $p)
{
	$result = mysql_query($s) or die("Can not query in xexec $p ".mysql_error());
	if ($result=="") {exit;}
	return $result;
}

/**
 * converts a tracking number into a gateway node to redirect to:
 * returns URL, or FALSE
 *
 * 5/1/08 - ssadedin: rewrote using pdo
 */
function tracking_to_node_guid ($t,&$guid)
{ 
  try {
    $db = DB::get();

    $nodes = $db->query("SELECT n.hostname, d.guid FROM tracking_number t, document d, document_location l, node n
                         WHERE tracking_number= ?
                         AND d.id = t.doc_id
                         AND l.document_id = d.id
                         AND n.node_id = l.node_node_id", array($t));

    if(count($nodes)==0)
      return FALSE;

    $n = $nodes[0];

    $guid = $n->guid;
    return $n->hostname; 
  }
  catch(Exception $e) {
    error_log("Failed to query tracking number $t: ".$e->getMessage());
    return FALSE;
  }
}

function tracking_redirect($gw,$guid,$tracking,$pin=false) //*****
{	
$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $auth="";
if (isset($_COOKIE['mc']))  // fixed 15 nov 06
{
  $c1 = $_COOKIE['mc'];
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

		  case 'auth'; $auth = $val; break;

		}

	}
}
if ($accid=='') $accid='0000000000000000';
if ($idp=='') $idp='pops';
//******* 	

$url = false;
if(($accid != "0000000000000000") &&  (can_resolve_guid($accid, $guid, "R"))) { // if resolved, user has access without pin - go straight there
    $url = "$gw/access?g=$guid&t=$tracking&a=$accid&at=$auth";
}
else {
  $url = "$gw/tracking.jsp?tracking=$tracking&accid=$accid&idp=$idp&auth=$auth";
  if($pin) {
    $url .= '&p='.sha1($pin);
  }
}

//$terryUrl = strong_url($url);
$terryUrl = $url;

$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway from track.inc.php via $url</title>
<meta http-equiv="REFRESH" content="0;url='$terryUrl'"></HEAD>
<body >
<p>
Please wait whilst we connect to the MedCommons Repository Gateway...
</p>
</body>
</html>
XXX;
echo $x;
	exit;
}
?>
