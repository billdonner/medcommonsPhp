<?php
require "dbparams.inc.php";
// wld 2/17/06 - added tracking numbers to ccrlog so as to support PINS properly
// wld 6/20/06 - including session.php
// ss  11/7/06 - changed to correctly resolve gateway based on accid and access permissions 
//             - added support for "dest" parameter
// ss  16/7/07 - added support for nf (notfound) parameter.  If not found, user redirected there.

require_once "utils.inc.php";
require_once "session.inc.php";
require_once "securelib.inc.php";

function xexec ($s, $p)
{
	$result = mysql_query($s) or die("Can not query in xexec $p ".mysql_error());
	if ($result=="") {exit;}
	return $result;
}

function try_redirect()
{
	// should not exit if successful, otherwise returns with an error code which is turned into a decent error message
	// start here
	dbconnect();
	$qs=$_SERVER["QUERY_STRING"];
	$guid=req('guid');
	$raw=req('raw');
	$tracking=req('tracking');
	$free=req('free');
	$mode=req('mode');
	$context=req('context');
	$dest=req('dest');
	
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $auth="";
	
	if(isset($_COOKIE['mc'])) {
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

  // Allow overide of auth
  if(isset($_REQUEST['auth']))
   $auth = $_REQUEST['auth'];

  // Try to find a node that can display the guid 
  // We support two separate auth token cookies.  One is set
  // by login (mc) and identifies the user.   The other is
  // an 'anonymous' cookie - it authorizes but doesn't identify the
  // user with a medcommons account.
  dbg("finding node for $guid using auth $auth");
  $node = find_node($guid,$auth);
  if($node === false) { // Not found with normal auth, is it found with open id auth?
    $openidAuth = isset($_COOKIE['mc_anon_auth']) ? $_COOKIE['mc_anon_auth'] : false;
    if($openidAuth) {
      dbg("resolving using anon auth ".$openidAuth);
      $node = find_node($guid,$openidAuth);
      $auth = $openidAuth;
    }
    
	if($node === false) { // Not found
	    dbg("node not found");
			return 1;
	}
  }
  

  $auth = $node->auth;
  dbg("resolved $guid to node ".$node->hostname." with auth $auth");
	
	$gw = $node->hostname;
	                 
	setcookie("mcgw", $gw, time()+3600,'/','.'.$GLOBALS['Script_Domain']);

  // OAuth parameters to be passed through
  $oauth = req('oauth_token');
  $identity = req('identity');
  $identity_type = req('identity_type');
  $name = req('identity_name');
  if($oauth) {
    $oauth_params = "&oauth_token=$oauth&identity=".urlencode($identity)."&identity_type=".urlencode($identity_type)."&identity_name=".urlencode($name);
  }
	
	if($raw == 'true') { // raw guid - do not pass viewer, go directly to jail
	  $url = "$gw/streamDocument.do?guid=$guid&accid=$accid&idp=$idp";
    if(isset($_REQUEST['dl']) && ($_REQUEST['dl']=='true')) {
      $url .= "&dl=true";
    }
	}
	else
	if($dest != "") {
	  $url = "$gw/$dest";
	  if(strpos($dest, "?")===FALSE) {
	  	$url.="?";
	  }
	  else
	    $url.="&";

    $url.="g=$guid&t=$tracking&m=$mode&c=$context&auth=$auth&at=$auth".($oauth?$oauth_params:""); 
    dbg($url);
    if(preg_match("/a=[0-9]{16}/",$url)===0) {
      $url.="&a=$accid";
    }
	}
	else
	if($tracking=='') // If no tracking number then get there directly by guid
	  $url = "$gw/access?g=$guid&a=$accid&m=$mode&c=$context&at=$auth"; 
	else // There is a tracking number - use track# interface
	  $url = "$gw/tracking.jsp?tracking=$tracking&accid=$accid&idp=$idp&context=$context";
	
	$strongUrl = $url;
	//$strongUrl = strong_url($url);
  if(isset($_REQUEST['nopage'])) {
    header("Location:  $strongUrl");
  }
  else {
 	$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway from gwredirguid via $url</title>
<meta http-equiv="REFRESH" content="0;url='$strongUrl'"></HEAD>
<body >
<p>
Please wait while we access patient records...
</p>
<img border=0 src = "/images/progressbar.gif" alt='thank you for your patience' />
</body>
</html>
XXX;
    echo $x;
  }
	exit;
}

// main program

$stat = try_redirect();
switch ($stat) {
	case 1:  $err="The specified document could not be located for the given user."; break;
	default: $err="Unknown Redirection Error";
}
if(($stat == 1) && isset($_REQUEST['nf'])) {
  header('Location: '.$_REQUEST['nf']);
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
