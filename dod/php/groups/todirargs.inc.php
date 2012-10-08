<?php 



function cleanreq ($x) { 		if (!isset($_REQUEST[$x])) return false; //wld 07sep06 - tough checking
return $_REQUEST[$x]; }
// these are the basic query parameter arguments that are passed around
//
//
$xid = cleanreq('xid');
$ctx = cleanreq('ctx');
$alias = cleanreq('alias');
$accid=  cleanreq('accid');
$contact = cleanreq('contact');
// these params control the formatting of output
$limit = cleanreq('limit');
$logo = cleanreq('logo');

?>