<?php

require_once "dbparamsmcextio.inc.php";
function blurb ($link,$tooltip,$text)
{ return <<<XXX
<a href="$link" onmouseover="this.T_WIDTH=200;this.T_FONTCOLOR='#003399'; return escape('$tooltip')">$text</a>
XXX;
}
function safechars ($x)
{
$avoid = array("'", ".", '"', "\\","=",":",")","\r","\n");
return str_replace($avoid, "*", $x);
}
function tooltip ($tooltip,$text)
{ 
$tooltip = safechars ($tooltip);
$text = safechars ($text);

return <<<XXX
<a href = "#"; onmouseover="this.T_WIDTH=200;this.T_FONTCOLOR='#003399'; return escape('$tooltip')">$text</a>
XXX;
}

function table_row ($r)
{	
$time = $r['timestamp'];
$sender = $r['sender'];

$tracking = $r['trackingnumber'];
if ($tracking !="") {
	$tr = "<a href=trackinghandler.php?trackingNumber=$tracking>$tracking</a>";
	$tracking = $tr;
}

$pin = $r['pin'];

$description = $r['description'];
$desc = tooltip($description,substr($description,0,50));

$version = $r['version'];
$useragent = $r['useragent'];
$upos = strpos($useragent," ");
if ($upos>0) $useragent = substr($useragent,0,$upos);

$email = $r['email'];
if ($email != "") $sender = $email;

	$ret = "<tr><td> $time </td><td> $sender </td><td> $tracking </td><td> $pin </td>
	<td> $desc </td><td> $version </td><td> $useragent </td></tr>";
	return $ret;
}

function table_dump ($t)
{
	//get standard parameters
	$limit = $_REQUEST['limit'];
	if ($limit=="") $limit=30;
	$filter = $_REQUEST['filter'];

	//connect to database

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or xmlend ("can not connect to mysql");

	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");

	$out = "<table border=1><tr><th>  time  </th><th>  sender  </th><th>  tracking  </th><th>  pin  </th>
	<th>  description  </th><th>  version  </th><th>  useragent  </th></tr>";

	if ($filter!="") $f = "Filter $filter"; else $f=''; // set up filter header
	$query = "SELECT * FROM cxpproblems ORDER BY id DESC LIMIT $limit";

	//build custom query
	$result = mysql_query ($query) or die ("can not query $t - ".mysql_error());
	$count = 0;
	if ($result!="") {
		// the while statement is generic
		while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$out.= table_row ($l);
			$count++;
		}

	}
	
	mysql_free_result($result);
	
	$out .='</table><script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>'; //remember to include the tooltips js stuff down at the very bottom
	return $out;
}
// main starts here

$content = table_dump("cxpproblems");

$x=<<<XXX
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>MedCommons CXP BloG</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="icon" href="../favicon.gif" type="image/gif">
<link rel="shortcut icon" href="../favicon.gif" type="image/gif">
<link href="../../css/header.css" rel="stylesheet" type="text/css">
<link href="../../css/main.css" rel="stylesheet" type="text/css">
</head>
<body> 
<div class="menu"> 
<ul>
<li id="mmenu1"><a href="../hp.html"><img src="../../images/mmtab_off_01.gif" border="0"></a></li>
<li id="mmenu2"><a href="logon.php?err=You%20must%20be%20logged%20in%20to%20reply%20to%20a%20CCR."><img src="../../images/mmtab_off_02.gif" border="0"></a></li>
<li id="mmenu4"><a href="logon.php?err=You%20must%20be%20logged%20in%20to%20view%20a%20CCR."><img src="../../images/mmtab_off_04.gif" border="0"></a></li>
</ul>
</div> 
<div id="topframe"><a href="../hp.html" target="_top"><img id="logo" src="../../images/logo_leftcorner.gif" border="0" alt="MedCommons" width="364" height="69"></a></div> 
<iframe id="trkframe" src="../trackform.htm" scrolling="no"></iframe> 
<div class="textmenu"> 
  <ul> 
    <li><a href="terms_of_use.htm">Terms of Use</a></li> 
    <li><a href="about.htm">About Us</a>&nbsp;&nbsp;||&nbsp;&nbsp;</li> 
    <li><a href="faq.htm">FAQ</a>&nbsp;&nbsp;||&nbsp;&nbsp;</li> 
  </ul> 
</div> 
<!-- Begin Page Content //-->


 
<div id="content"> 
  <h1>MedCommons CXP Blog</h1> 
  $content
</div> 



<!-- End Page Content //--> 
<div id="footer">&copy; 2006 MedCommons all rights reserved.&nbsp;&nbsp;&nbsp;page last modified:&nbsp; 
  <script type="text/javascript"> document.write(document.lastModified); </script> 
</div> 

<p class=MsoNormal>&nbsp;</p>
<p class=MsoNormal>&nbsp;</p>
</div>
</body>
</html>
XXX;

echo $x;
?>