<?php
//require_once 'tracker_dictionary.inc.php';
function makedictionary ($db)
{
	//echo "showtrackers: opening $x<br>";
	$query = 'select * from tracker_dictionary';
	$result = sqlite_query($db, $query);
	$count = 0;
	while (true) {
		$l = sqlite_fetch_array($result,SQLITE_ASSOC);
		if ($l===false) break;
		$td[$count][0]= $l['tracker'].":".$l['units'];
		$td[$count][1]= $l['infourl'];
		$td[$count][2]= $l['graphicurl'];
		$td[$count][3]= $l['ginputurl'];

		$count++;
	}
	return $td;
}
$time = time();
$accid = $_POST['accid']; // must get this for continuity
$trackind = $_POST['tracker']; // this is actually a numeric into the tracker_dictionary

//echo "tracker is $tracker<br>";
if ($trackind!=''){
	require_once 'dblocation.inc.php';

	if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))
	{ 
		$tracker_dictionary = makedictionary($db); //recreate = ugh

		$tracker = $tracker_dictionary[$trackind][0];
		$infourl = $tracker_dictionary[$trackind][1];
		$graphurl = $tracker_dictionary[$trackind][2];
		$ginputurl = $tracker_dictionary[$trackind][3];

		$x=<<<XXX
 REPLACE INTO tracker_info Values ('$tracker','$infourl','on','on','','$time','','$graphurl','$ginputurl');
XXX;
		sqlite_query($db,$x) or
		die ("Can't update into tracker_info ".sqlite_error_string(sqlite_last_error($db)));
		//echo 'Error: '.sqlite_error_string(sqlite_last_error($db));

	}

	else {
		die($sqliteerror);
	}
}
header("Location: trackerEditor.php?accid=$accid");

exit;

?> 
