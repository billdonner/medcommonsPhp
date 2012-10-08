<?php
require_once '../../dblocation.inc.php';

$time = time();
$tracker = $_POST['tracker'];
$accid = $_POST['accid'];


if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
	$values=explode(',',file_get_contents($_FILES['userfile']['tmp_name']));
} else {
	echo "File upload failure: ";
	echo "filename '". $_FILES['userfile']['name'] . "'.";
	exit;
}
$count = count($values);
if ($count!=0){

	if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))
	{
		for ($i=0; $i<$count; $i++){
			$val = $values[$i];
//			echo "Insert $tracker $val <br>";
			sqlite_query($db, "INSERT INTO trackers VALUES ('$tracker','$val','$time')") or 
							die ("Can't insert into trackers");
			sqlite_query($db, "UPDATE tracker_info SET value='$val', tracktime='$time' 
				WHERE tracker='$tracker' ") or die ("Can't insert into tracker_info");
			
		}
	}
	else {
		die($sqliteerror);
	}
}
header("Location: grabInput.php?accid=$accid&tracker=$tracker");
?> 
