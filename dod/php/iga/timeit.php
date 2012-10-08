<?php
// flushes were broken on www, who knows?
if (isset($_REQUEST['repeat']))
$repeat = $_REQUEST['repeat']; else $repeat=1;
$url = $_REQUEST['url'];
echo "<div><h3>pinging $url</h3>
<ul>";
//ob_flush();
//lush();
for ($i=1; $i<=$repeat; $i++)
{
$time1 = microtime(true);
$str = file_get_contents($url);
$time2 = microtime(true);
$elapsed = round($time2-$time1,2);
$len = round(strlen ($str)/1024,2);

$out = <<<XXX
<li>request $i took $elapsed secs $len KB</li>
XXX;
echo $out;
////ob_flush();
//flush();
}
$out = "</ul>
</div>";
echo $out;
?>