<?php
require_once  "showgeneric.inc.php";

//main
$central = $_GET['ct'];
$query =  "SELECT * from spprobes where (cthost = '$central')";
$table = "spprobes";
$title = "Central $central";

$x = new showgeneric(); // only one object here, only one page

$x->openit($title);

$x->doit($table,$query);


$query =  "SELECT * from xioprobes where (cthost = '$central')";
$table = "xioprobes";

$x->doit($table,$query);

$x->closeit();

?>