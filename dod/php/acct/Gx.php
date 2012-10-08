<?php

require_once "alib.inc.php";

require_once "layout.inc.php";

require_once "gx.inc.php";

function parse_error ()
{
	die("Internal screwup");
}



// donner 09 oct 06 - Group Services
// accept incoming REST call, assemble ccrs and docs ,  return XML
//
// args: t=template
//
//		 c=command string
//		 mckey = medcommons widget key

if (!isset($_REQUEST['t'])) die("Must supply &t=template"); else
$t = $_REQUEST['t'];

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database

// pull the template in and parse it

$s = file_get_contents("/var/www/php/groups/$accid-$t-gx.htm");

$p1 = strpos($s,$__top);
if ($p1===false) parse_error();

$p2 = strpos ($s,$__middle);
if ($p2===false) parse_error();

$p3 = strpos ($s,$__bottom);
if ($p3===false) parse_error();

$headercode = substr($s,$p1+$__toplen,$p2-$__toplen-$p1);

$bodycode = substr($s, $p2+$__middlelen+2,$p3-$p2-$__middlelen-2);

//
//ok, now re-assemble into a proper medcommons page
//


echo std("Group Landing Page",
"Group Landing Page for $t",
$headercode,
false, 
stdlayout ( $bodycode.
"<p>and some fixed medcommons stuff can go here as well</p>"));

?>