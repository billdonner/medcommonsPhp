<?php
$url = $_REQUEST['u'];
header ("Location: $url");
echo "Please wait while we redirect to $url";
?>