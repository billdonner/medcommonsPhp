<?php

require_once "appinclude.php";


$ob="<html><head><meta http-equiv='refresh' content='60'></head><body style='font-size:12px'>";
$client = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];
$server =  isset($_SERVER['SCRIPT_URI']) ? $_SERVER['SCRIPT_URI'] : $_SERVER['HTTP_HOST'];
$time = strftime ('%T %D');

$ob.= "$time - $server was contacted by $client <br/>";

$ob.="<i>This is the hbmonitor itself, and has little else useful to report. You can remove this from the list of systems to monitor by altering index.php";
$ob.="</body></html>";
echo $ob;
?>