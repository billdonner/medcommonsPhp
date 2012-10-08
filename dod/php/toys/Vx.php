<?php

// donner 09 sep 06
// accept incoming REST call, assemble ccrs and docs ,  return XML
//
// args: t=template
//		 a=pipe delimited list of args
//		 c=command string
//		 mckey = medcommons widget key

if (!isset($_REQUEST['t'])) die("Must supply &t=template"); else
$t = $_REQUEST['t'];

if (!isset($_REQUEST['c'])) die("Must supply &c=command (details to be supplied)"); else
$c= $_REQUEST['c'];

// now distinguish between GET and POST calling variants
if (isset($_GET['c'])) {
	if ($_GET['c']!='code'){
		// okay this is the get variant, unpipe arg a
		if (!isset($_GET['a'])) die("Must supply &a=arglist (a pipe delimited string)"); else
		$a = $_GET['a'];
		$arg=  explode('|',$a); // shred arg list
		for ($i=0;$i<10;$i++) if (!isset($arg[$i]))$arg[$i]='';
	}
}
else
{
	// this is the post variant, put in discrete args
	$arg[0]=isset($_POST['arga'])?$_POST['arga']:'';
	$arg[1]=isset($_POST['argb'])?$_POST['argb']:'';
	$arg[2]=isset($_POST['argc'])?$_POST['argc']:'';
	$arg[3]=isset($_POST['argd'])?$_POST['argd']:'';
	$arg[4]=isset($_POST['arge'])?$_POST['arge']:'';
	$arg[5]=isset($_POST['argf'])?$_POST['argf']:'';
	$arg[6]=isset($_POST['argg'])?$_POST['argg']:'';
	$arg[7]=isset($_POST['argh'])?$_POST['argh']:'';
	$arg[8]=isset($_POST['argi'])?$_POST['argi']:'';
	$arg[9]=isset($_POST['argj'])?$_POST['argj']:'';

}
// only require the mckey if the command is 'cxp'

if ($c=='cxp')
if (!isset($_REQUEST['mckey'])) die("Must supply &mckey for cxp option");
$sha1=false;$accid=false;$email=false; //prepare for the worst
if (isset($_REQUEST['mckey']))
{
	// if supplied, use it
	$mckey= $_REQUEST['mckey'];
	$mckeyparts = explode('|',base64_decode($mckey));
	if (isset($mckeyparts[0])) $sha1 = $mckeyparts[0];
	if (isset($mckeyparts[1])) $accid = $mckeyparts[1];
	if (isset($mckeyparts[2])) $email = $mckeyparts[2];
	if ($sha1===false || $accid===false || $email===false) die ('This mckey appears invalid, try again');
}
//
// if the template argument is multi-componented then decompose it
if (strpos($t,':')!==false) list($selector,$template) = explode (':',$t); else
{ $selector = $accid; $template = $t;}
if ($c=='code')
{// special case, return a page of php
	$str = file_get_contents("/var/www/php/funcs/$selector-$template-vxm.ccr.php");
	header ("Content-type: text/plain");
	echo $str;
	exit;
}

// pull the template in and call the standard functor, passing the command code

require_once "/var/www/php/funcs/$selector-$template-vxm.ccr.php";
list ($sig,$xml) = vx($arg[0],$arg[1],$arg[2],$arg[3],$arg[4],$arg[5],$arg[6],$arg[7],$arg[8],$arg[9]);
// use the command code to figure out what the caller wants to get back
switch ($c){

	case'form'	:{
		if (isset($mckey)) $mckey = "<tr><td>mckey</td><td><i>hidden</i><input type='hidden' name='mckey' value='$mckey' /></td></tr>\r\n";
		else $mckey='';
		$gmt = gmstrftime("%b %d %Y %H:%M:%S");
		$uri = htmlspecialchars($_SERVER["REQUEST_METHOD"].' '.$_SERVER ['REQUEST_URI']);
		$aout ="processed by ".$_SERVER['HTTP_HOST'].' '.$_SERVER['SERVER_ADDR'].':'.
		$_SERVER['SERVER_PORT']." $gmt GMT";

		$out = <<<XXX
<!-- form generated from template $t $uri $aout -->
<h4>test form generated from function $t $aout</h4>
<i>View source, copy and paste to your editor, customize this as you see fit, and pasteinto your own application, wiki, blog, or web site</i>
<form method='POST' action='Vx.php'>
<table>
$mckey
<tr><td>template</td><td><i>hidden $t</i><input type='hidden' name='t' value='$t' /></td></tr>\r\n
<tr><td>command</td><td><i>hidden hardwired for now to 'do'</i><input type='hidden' name='c' value='do' /></td></tr>\r\n
<tr><td>arga</td><td><input type=text name='arga' value='ARGA' /></td></tr>\r\n
<tr><td>argb</td><td><input type=text name='argb' value='ARGB' /></td></tr>\r\n
<tr><td>argc</td><td><input type=text name='argc' value='ARGC' /></td></tr>\r\n
<tr><td>argd</td><td><input type=text name='argd' value='ARGD' /></td></tr>\r\n
<tr><td>arge</td><td><input type=text name='arge' value='ARGE' /></td></tr>\r\n
<tr><td>argf</td><td><input type=text name='argf' value='ARGF' /></td></tr>\r\n
<tr><td>argg</td><td><input type=text name='argg' value='ARGG' /></td></tr>\r\n
<tr><td>argh</td><td><input type=text name='argh' value='ARGH' /></td></tr>\r\n
<tr><td>argi</td><td><input type=text name='argi' value='ARGI' /></td></tr>\r\n
<tr><td>argj</td><td><input type=text name='argj' value='ARGJ' /></td></tr>\r\n
<tr><td>submit</td><td><input type=submit name='submit' value='try it' /></td></tr>\r\n
</table>
</form>
<!-- end of form generated from template $t -->
XXX;
		header ("Content-type: text/html");
		break;
	}

	case 'do': {
		$gmt = gmstrftime("%b %d %Y %H:%M:%S");
		$uri = htmlspecialchars($_SERVER["REQUEST_METHOD"].' '.$_SERVER ['REQUEST_URI']);
		$aout ="<details>processed by ".$_SERVER['HTTP_HOST'].' '.$_SERVER['SERVER_ADDR'].':'.
		$_SERVER['SERVER_PORT']." $gmt GMT</details>";
		$aout.= "<requesturi>\n".$uri."</requesturi>\n";
		$out = <<<XXX
<vxreturn>
$aout
	<sig>$sig</sig>
	<ccr>$xml</ccr>
	<status>success</status>
</vxreturn>
XXX;
		header ("Content-type: text/xml");
		break;
	}

	case 'xml': {
		$out = $xml;
		header ("Content-type: text/xml");break;
	}

	case 'cxp': {
		require_once "cxpputstring.inc.php";
		$bout= CxpPutString('https://cxp.medcommons.net/router/CxpRestServlet','mykey',$xml);
		$gmt = gmstrftime("%b %d %Y %H:%M:%S");
		$uri = htmlspecialchars($_SERVER["REQUEST_METHOD"].' '.$_SERVER ['REQUEST_URI']);
		$aout ="<details>processed by ".$_SERVER['HTTP_HOST'].' '.$_SERVER['SERVER_ADDR'].':'.
		$_SERVER['SERVER_PORT']." $gmt GMT</details>";
		$aout.= "<requesturi>\n".$uri."</requesturi>\n";
		$out = <<<XXX
<vxreturn>
$aout
	<sig>$sig</sig>

	<status>$bout</status>
</vxreturn>
XXX;
		//	<ccr>$xml</ccr>
		header ("Content-type: text/xml");
		break;
	}

	default: break;
}

echo $out;
?>