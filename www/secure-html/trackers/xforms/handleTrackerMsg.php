<?php
$html = <<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>PHP and XForms</title>
</head>
<body>
XXX;

// We start here. If there is some posted data, format it.
//  for now, we'll take input from a file
//$req = $GLOBALS['HTTP_RAW_POST_DATA'];
//$xml = simplexml_load_string($req);

$f=$_REQUEST['file'];
$xml = simplexml_load_file($f);

require_once 'dbloc.inc.php';

if ($db = sqlite_open(get_tracker_db(), 0666, $sqliteerror))
{

	$time = time();
	$sourceid = $xml->sourceid;
	foreach ($xml->tracker as $tracker)
	{
		$html.=  "$sourceid, $tracker->name, $tracker->value ,<br>";

		sqlite_query($db, "INSERT INTO trackers VALUES ('$tracker->name', '$tracker->value',
    			'$time')") or die ("Can't insert into trackers");
		sqlite_query($db, "UPDATE tracker_info SET value='$value', tracktime='$time'
				WHERE tracker='$tracker' ") or die ("Can't insert into trackers");

	}
}

else {
	die($sqliteerror);
}


$html.=<<<XXX

</body>
</html>
XXX;
echo $html;
?>