<?php
require_once '../../dblocation.inc.php';


$time = time();
$tracker = $_GET['tracker'];
$accid = $_GET['accid'];

$query = 'select * from trackers';
if ($tracker!='') $query.=" where tracker='$tracker'";
$query.=" ORDER BY tracktime DESC LIMIT 10";

if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))

{
	$result = sqlite_query($db, $query);
	$count = 0;
	$innerhtml = "<h4>Recent values for $tracker</h4><table class='trackertable'>";

	while (true) {
		$l = sqlite_fetch_array($result,SQLITE_ASSOC);
		if ($l===false) break;
			if ($l=='') break;
		$count++;
//	   	echo "tracker=".$l['tracker']." value=".$l['value']." units=".$l['units'].
//	   	" trackertime=".$l['tracktime']."<br>";
		$tracker = $l['tracker'];
		$units = $l['units'];
		$value = $l['value'];
		$tracktime = $l['tracktime'];
		$af = strftime("%c",$tracktime);//strftime("%D %T");

		$innerhtml.="
<tr><td class='tracker'>$tracker</td><td class='trackerunit'>($units)</td>
<td>$value</td><td>$af</td></tr>";
	} 
		$innerhtml .= "</table>";
} else {
	die($sqliteerror);
}

$html= <<<XXX
<html><head><title>MedCommons- Get Tracker Input for $tracker</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "../../gadget.css"; </style>

</head>
<body><table><tr><td><a href="index.html" ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="My Trackers" /></a>
                </td><td>My Trackers <small><i>enter new values</i>
                &nbsp;<a href = 'help.html' target="_help">help</a></small></td></tr>
                </table><table class='trackertable'>
$innerhtml

<form method=post action=grabDone.php >
<input type=hidden name=tracker value='$tracker' />
<input type=hidden name=accid value='$accid' />
<h4>Enter new value for $tracker</h4>
<table class='trackertable'>
<tr><td class='tracker'>$tracker</td><td class='trackerunit'>()</td>
<td><input type=text name=value /><input type=submit value='Go' /></td></tr>
</table>
</form>
<p>
or</p>
<!-- The data encoding type, enctype, MUST be specified as below -->
<form enctype="multipart/form-data" action="grabCSV.php" method="POST">
	<input type=hidden name=tracker value='$tracker' />
	<h4>Load new values for $tracker from CSV file</h4>
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
    <!-- Name of input element determines name in $_FILES array -->
    CSV file: <input name="userfile" type="file" />	
    <input type="submit" value="Load File" />
</form>
</body>
</html>
XXX;

echo $html;
?>
