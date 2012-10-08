<?php
require_once "setup.inc.php";

function connect_db()
{
	
	mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User']) or err("Error connecting to database.");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die("can not connect to database $db");

}

// don't call the facebook initialization stuff just open the db and rip thru it

function getpagepart($s)
{
	$str = strrchr($s,'/'); // find the last
	return substr($str,1);
}

function emit($ord,$topic,$extra, $url)
{
	global $DESTINATION_DIRECTORY,$TOPICS_URL;
	if ($extra!='') $metadata = "<p>The NLM metadata associated with this request is : $extra</p>"; else $metadata='';
	
	
	$filecontents = <<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>MedCommons / CCR Commons Topic $topic</title>
	<meta http-equiv="refresh" content="0;url=$TOPICS_URL?ord=$ord" />
	<meta name="author" content="MedCommons, Inc." />
           <meta name="description" content="Health Topic $topic - MedCommons" />
           <meta name="keywords" 
                content=" $extra medcommons, personal health records,ccr, phr, privacy, patient, health, records, medical records,emergencyccr" />
           <meta name="robots" content="all" />
           </head>
	<body><p>Redirecting to Topic $topic</p>$metadata <p>You can also try <a href='$TOPICS_URL?ord=$ord' >here</a></p>
	</body>
</html>
XXX;

	file_put_contents ("$DESTINATION_DIRECTORY/$url",$filecontents);
	
}
connect_db();	
if ((!isset($_REQUEST['action']))||(!isset($_REQUEST['out']) ) )die("usage: ?action=-redirecturl- &out=-directory to store html files-");
$DESTINATION_DIRECTORY = $_REQUEST['out'];
$TOPICS_URL = $_REQUEST['action'];
$q = "select * from topics";
$result = mysql_query($q) or die ("Cant $q ".mysql_error());
while ($r=mysql_fetch_object($result))
{
	$s = getpagepart ($r->nlmurl); 
	emit ($r->ord, $r->nlmtopic, $r->nlmxtra,  $s);
	
}
echo "All done, have a look at $DESTINATION_DIRECTORY";
?>