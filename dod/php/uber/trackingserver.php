<?php
// allocate tracking numbers in large chunks, via a REST CALL to anyone who asks for some
//
require_once 'dbparamsiga.inc.php';
header('Content-type: text/plain');
$arg=$_REQUEST['arg'];
list($server,$comment)=explode('|',base64_decode($arg));
$db = $GLOBALS['DB_Database'];

mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");

$tb = rand(1000,9999).rand(1000,9999);

$insert ="Insert into trackingblocks set trackingblock='$tb',server='$server',comment='$comment'";
mysql_query($insert) or die("Cant insert $insert ".mysql_error()); // must add loop on err and retry
echo $tb; // that's all that goes back, really
?>