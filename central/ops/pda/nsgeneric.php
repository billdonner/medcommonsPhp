<?php
require_once  "nsgeneric.inc.php";

//main
$title = $_GET['title'];
$table = $_GET['table'];
$uri = $_GET['uri'];

$x = new nsgeneric();
$x->doit($title,$table,$uri)


?>