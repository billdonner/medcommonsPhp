<?php
require_once '../../dblocation.inc.php';


require_once 'mcsparklines.inc.php'; // sparkline graph	

list ($tracker,$units, $height, $width, $linewidth, $bgcolor,
$showmin,$showmax,$showlast,$renderquality,$accid) = explode('|',base64_decode($_GET['t']));
$query = 'select * from trackers';
$data = array();
if ($tracker!='') $query.=" where tracker='$tracker'";
$db=get_tracker_db($accid);
//echo "Openinig $db \r\n";
if ($db = sqlite_open($db, 0666, $sqliteerror))
 { 
    $result = sqlite_query($db, $query);
    $count = 0;
    while (true) {
    	$l = sqlite_fetch_array($result,SQLITE_ASSOC);
    	if ($l===false) break;
    	    	    	if ($l=='') break;

    	$count++;
    	$highlight = ($l['units']!=$units);
		$data[$l['tracktime']]=array($l['value'],$highlight);
    } 
if ($showmin=='-none-') $showmin='';
if ($showmax=='-none-') $showmax='';
if ($showlast=='-none-') $showlast='';

	mcsparkline ($data, '', $width,$height,$linewidth,
						$showmin,$showmax,$showlast,'yellow','high',make_sparkline_log_name($accid));						
} else {
    die($sqliteerror);
}
?>
