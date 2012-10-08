<?php

require_once "healthbook.inc.php";

// don't call the facebook initialization stuff just open the db and rip thru it

function getpagepart($s)
{
	$str = strrchr($s,'/'); // find the last
	return substr($str,1);
}

function emit($ord,$topic,$url)
{
	global $DESTINATION_DIRECTORY,$TOPICS_URL;
	
	$filecontents = <<<XXX
	<html><head><title>MedCommons HealthBook Topic $topic</title>
	<meta http-equiv="refresh" content="0;url=$TOPICS_URL?ord=$ord">
	<body>Redirecting to Topic $topic<br/><small>You can also try <a href='$TOPICS_URL?ord=$ord' >here</a></small></body></html>
XXX;

	file_put_contents ("$DESTINATION_DIRECTORY/$url",$filecontents);
	
}
connect_db();	
if ((!isset($_REQUEST['action']))||(!isset($_REQUEST['out']) ) )die("usage: ?action=-callbackurl- &out=-directory to store html files-");
$DESTINATION_DIRECTORY = $_REQUEST['out'];
$TOPICS_URL = $_REQUEST['action'];
$q = "select * from topics";
$result = mysql_query($q) or die ("Cant $q ".mysql_error());
while ($r=mysql_fetch_object($result))
{
	$s = getpagepart ($r->nlmurl); 
	emit ($r->ord, $r->nlmtopic, $s);
	
}
echo "All done, have a look at $DESTINATION_DIRECTORY";
?>