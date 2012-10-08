<?php
// this file is included by create Tracker Db
require_once 'dblocation.inc.php';
if ($___file!='') $f=$___file;

$x = simplexml_load_file($f);
echo "Loading $f <br>";
echo $x->name."<br>";
echo "published by: ".$x->publisher."<br>";
echo "graphics: ".$x->graphics."<br>";
$publisher=trim ($x->publisher);
$id = trim($x->id);
$graphics = trim ($x->graphics);
$ginput = trim ($x->input);
if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror)){
	$t = $x->trackers;
	foreach ($t->tracker as $tracker ) {
		echo "adding: ".$tracker->abbrev."<br>";
		$abbrev = trim($tracker->abbrev);
		$units = trim ($tracker->units);
		$info = trim ($tracker->info);

		$query=<<<XXX
REPLACE INTO tracker_dictionary Values ('$abbrev','$units','$info','$publisher','$id','$graphics','$ginput');
XXX;
		sqlite_query($db,$query) or
		die ("Can't update into tracker_dictionary ".sqlite_error_string(sqlite_last_error($db)));
	}
}
else {
	die($sqliteerror);
}

?>