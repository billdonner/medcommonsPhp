<?php
$time = time();
$tracker = $_GET['tracker'];
$accid = $_GET['accid'];
$query = 'select * from trackers';
if ($tracker!='') $query.=" where tracker='$tracker'";
require_once 'dblocation.inc.php';
$x = get_tracker_db($accid);
echo "showtrackers: opening $x<br>";
if ($db = sqlite_open($x, 0666, $sqliteerror))
{
	$result = sqlite_query($db, $query);
	$count = 0;
	echo "Query is $query<br>";
	while (true) {
		$l = sqlite_fetch_array($result,SQLITE_ASSOC);
		if ($l===false) break;
		$count++;
		echo "tracker=".$l['tracker']." value=".$l['value']." units=".$l['units']." trackertime=".$l['tracktime']."<br>";
	}
	echo "Total of $count trackers found<br>";
	$query = 'select * from tracker_properties';
	$result = sqlite_query($db, $query);
	$count = 0;
	while (true) {
		$l = sqlite_fetch_array($result,SQLITE_ASSOC);
		if ($l===false) break;
		$count++;
		echo "bgcolor=".$l['bgcolor']." height=".$l['height']." wdith=".$l['width'].
		" linewidth=".$l['linewidth']." showmin=".$l['showmin']." showmax=".$l['showmax'].
		" showlast=".$l['showlast']." renderquality=".$l['renderquality']."<br>";
	}
	echo "Total of $count tracker properties found<br>";
	$query = 'select * from tracker_info';
	$result = sqlite_query($db, $query);
	$count = 0;
	while (true) {
		$l = sqlite_fetch_array($result,SQLITE_ASSOC);
		if ($l===false) break;
		$count++;
		echo "tracker=".$l['tracker']." units=".$l['units']." display=".$l['show_display'].
		" edit=".$l['allow_input']." older=".$l['edit_older']." lastval=".$l['value']." gurl=".$l['graphicurl']." ginputurl=".$l['ginputurl']."<br>";
	}
	echo "Total of $count tracker info found<br>";
	$query = 'select * from tracker_dictionary';
	$result = sqlite_query($db, $query);
	$count = 0;
	while (true) {
		$l = sqlite_fetch_array($result,SQLITE_ASSOC);
		if ($l===false) break;
		$count++;
		echo "tracker=".$l['tracker']." units=".$l['units']." info=".$l['infourl'].
		" publisher=".$l['publisher']." id=".$l['id']." gurl=".$l['graphicurl']." ginputurl=".$l['ginputurl']."<br>";
	}
	echo "Total of $count tracker dictionary entries found";

} else {
	die($sqliteerror);
}

?> 
