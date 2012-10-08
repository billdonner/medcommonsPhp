<?php 



function cleanreq ($x) { return $_REQUEST[$x]; }
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