<?php
$redir = $_GET['to'];
$arg = $_GET['a'];

$url = "$redir?a=$arg";

header("Location: $url");
echo $url;
?>