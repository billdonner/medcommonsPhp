<?php
	$str= @file_get_contents($url);//file2($gwprot,$gwhost,$gwport,$gwfile);//@file($xmlFile);@file($xmlFile);//
	if ($str===FALSE) {$str='nine';}

	$count = strlen($str);
$id = $_REQUEST['id'];
$url = $_REQUEST['url'];
	$str= @file_get_contents($url);//file2($gwprot,$gwhost,$gwport,$gwfile);//@file($xmlFile);@file($xmlFile);//
	if ($str===FALSE) {$str='????';}
	$time = date("H:i:s");                         
echo "<div id='$id'>";
echo $str;
echo "</p></div>";

?>