<?php
require_once  "showgeneric.inc.php";

//main
$gw = $_GET['gw'];
$query =  "SELECT * from gatewayprobes where (cthost = '$gw')";
$table = "gatewayprobes";
$title = "Gateway $gw";


$x = new showgeneric();
$x->openit($title);
$x->doit($table,$query);
$x->closeit();
?>