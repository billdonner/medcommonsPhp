<?php
require_once 'dblocation.inc.php';

// misc completition routines for trackers.php button handling
//
// preview, save, and all delete and clear buttons come here
//
function clear_tracker($db,$tracker)
{
	$x = "DELETE FROM trackers WHERE tracker='$tracker'";
	sqlite_query($db,$x) or
	die ("Can't delete from tracker_info ".sqlite_error_string(sqlite_last_error($db)));
}
function do_clear_tracker($accid,$tracker)
{
	if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))
	{
	clear_tracker($db,$tracker);
	header("Location: trackerEditor.php?accid=$accid"
	);
	}
}
function do_delete_tracker($accid,$tracker)
{
	if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))
	{
	clear_tracker($db,$tracker);
	$x = "DELETE FROM tracker_info WHERE tracker='$tracker'";
	sqlite_query($db,$x) or
	die ("Can't delete from tracker_info ".sqlite_error_string(sqlite_last_error($db)));
	
	header("Location: trackerEditor.php?accid=$accid");}
	
}
	


// start here by pickin up arguments
$accid = $_POST['accid']; // must get this for continuity


$args = $_POST['argvals'];
list ($alltrackers,$allunits,$height,$width,$linewidth,$bgcolor,$showmin,$showmax,$showlast,$renderquality,$accid)= explode('|',base64_decode($args));
$bgcolor = $_POST['bgcolor']; // overrid supplied value with selection box
$height = $_POST['height']; // overrid supplied value with selection box
$width = $_POST['width']; // overrid supplied value with selection box
$linewidth = $_POST['linewidth']; // overrid supplied value with selection box
$showmin = $_POST['showmin']; // overrid supplied value with selection box
$showmax= $_POST['showmax']; // overrid supplied value with selection box
$showlast = $_POST['showlast']; // overrid supplied value with selection box
$renderquality = $_POST['renderquality']; // overrid supplied value with selection box

$trackers = explode(',',$alltrackers);
$units = explode(',',$allunits);
$count = count($trackers);


// CASE 1: USER CLICKS SAVE

if ('save'==$_POST["btSave"])
{	$time = time();

if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))
{
	sqlite_query($db, "UPDATE tracker_properties
		 Set bgcolor ='$bgcolor', height='$height', width='$width',
	         	linewidth='$linewidth',
				showmin='$showmin', showmax='$showmax', showlast='$showlast', 
				renderquality = '$renderquality',tracktime='$time'
		  		where Sequence='*****'")
		or 
	die ("Can't update tracker_properties ".sqlite_error_string(sqlite_last_error($db)));

	for ($i=0; $i<$count; $i++)
	{ $display = $_POST["cbDisplay$i"];
	$edit = $_POST["cbEdit$i"];
	$older = $_POST["cbEditOld$i"];
	$x=<<<XXX
		 UPDATE tracker_info
		 Set units='$units[$i]',show_display='$display',
		               allow_input='$edit',edit_older='$older', tracktime='$time'
		               Where tracker = '$trackers[$i]';
XXX;

	sqlite_query($db,$x) or
	die ("Can't update into tracker_info ".sqlite_error_string(sqlite_last_error($db)));
	}
	header("Location: trackerEditor.php?accid=$accid");
}
else {
	die($sqliteerror);
}
}
else { // not save
 // see if delete or clear was pressed
 for ($i=0; $i<$count; $i++)
{
	if ($_POST["btDelete$i"]=='delete') do_delete_tracker($accid,$trackers[$i]); // exits
	else 	if ($_POST["btClear$i"]=='clear') do_clear_tracker($accid,$trackers[$i]); //exits
}





// preview
$html= <<<XXX
<html><head><title>MedCommons - My Tracker Preview</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "gadget.css"; </style>
</head>
<body>
<table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="My Trackers" /></a>
                </td><td>My Trackers <small><i>click to input</i>
                <a href = 'trackerEditor.php?accid=$accid'>edit</a>
                &nbsp;<a href = 'help.html' target="_help">help</a></small></td></tr>
                </table><table class='trackertable'>
XXX;

for ($i=0; $i<$count; $i++)
{
/* dont allow editing in preview mode

	*/
	$prefix = $postfix = '';

	if ($_POST["cbDisplay$i"]=='on'){
		$uargs=("$trackers[$i]|$units[$i]|$height|$width|$linewidth|$bgcolor|$showmin|$showmax|$showlast|$renderquality|$accid");
		$args = base64_encode($uargs);
		$html.= <<<XXX
<tr><td class='tracker' align='right'><a target='_new' href='$units[$i]'>$trackers[$i]</a></td>
<td>$prefix
<img src="/plugins/mc/graphTracker.php?accid=$accid&t=$args" alt="$uargs"/>
$postfix</td></tr>
XXX;
}
}
$html.= <<<XXX
</table>
</body>
</html>
XXX;

echo $html;
}
?>
