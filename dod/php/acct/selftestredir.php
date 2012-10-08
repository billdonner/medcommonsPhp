<?
require_once "alib.inc.php";

$gw=allocate_gateway(0);

header("Location: $gw/SelfTest.action");

?>
