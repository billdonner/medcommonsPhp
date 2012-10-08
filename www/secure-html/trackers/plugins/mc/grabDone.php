<?php
require_once '../../dblocation.inc.php';


$time = time();
$tracker = $_POST['tracker'];
$value = $_POST['value'];
$accid = $_POST['accid'];


if ($value!=''){

	if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))
	{
		sqlite_query($db, "INSERT INTO trackers VALUES ('$tracker','$value',
    			'$time')") or die ("Can't insert into trackers");
		sqlite_query($db, "UPDATE tracker_info SET value='$value', tracktime='$time' 
				WHERE tracker='$tracker' ") or die ("Can't insert into trackers");

	}

	else {
		die($sqliteerror);
	}
}
header("Location: grabInput.php?accid=$accid&tracker=$tracker");
?> 
