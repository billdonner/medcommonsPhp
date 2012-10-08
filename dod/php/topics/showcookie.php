<?php


require_once "tlib.inc.php";
require_once "mlib.inc.php";

$p=testif_logged_in(); 
if ($p===false) {header ("Location: iclinfo.php"); exit;}
list($accid,$fn,$ln,$email,$idp,$cookie) =$p;

echo "MC Cookie contents: Account: $accid, First: $fn, Last: $ln, Email: $email, Idp: $idp <br>";
echo "Whole cookie is: $cookie";

?>
