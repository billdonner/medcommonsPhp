<?php

// find all of our trackers
//

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
		$td[$count][0]= $l['tracker']."-".$l['units'];
		$td[$count][1]= $l['infourl'];
		$td[$count][2]=$l['ginputurl'];
		$count++;
	}
	return $td;
}




function makeselect($td) // builds select statement from tracker dictionary
{ //  print_r($td);
	$s='';
$count = count($td);
//echo "in makeselect count $count";

for ($i=0; $i<$count; $i++){
//	echo "trackerEditor: makeselect $i < $count ";
	$s.="<option value='$i'"; if ($i==0) $s.="selected='selected'"; $s.= '>'.$td[$i][0];}
	return $s;
}


function prop ($label,$name,$vals,$selected)
{
	$x=$label; $x.="</td><td><select name='$name'>";
	$count = count($vals);
	for ($i=0; $i<$count; $i++)
	{	$sel = ($selected==$vals[$i])?' selected="selected" ':'';
	$x.="<option value='$vals[$i]' $sel>$vals[$i]\r\n";
	}
	$x.="</select>";
	return $x;
}

// start here
$query = 'select * from tracker_info';

require_once 'dblocation.inc.php';

$accid = $_REQUEST['accid'];
//echo "trackerEditor: opening db";
if ($db = sqlite_open(get_tracker_db($accid), 0666, $sqliteerror))
{
	$result = sqlite_query($db, $query);
	if (!$result) die("No table?");
	$count = 0;
	while (true) {
		$l = sqlite_fetch_array($result,SQLITE_ASSOC);
		if ($l===false) break;
		$trackers[$count] =$l['tracker'];
		$units [$count]=$l['units'];
		$display [$count]=$l['show_display'];
		$edit [$count] = $l['allow_input'];
		$older [$count] =$l['edit_older'];
		$gurl [$count] =$l['graphicurl'];
		$ginputurl [$count] =$l['ginputurl]'];

		$alltrackers.=$l['tracker'].',';
		$allunits.=$l['units'].',' ;
		$count++;

	}
	//    echo "Total of $count trackers found";
	$allunits = substr($allunits,0,strlen($allunits)-1);
	$alltrackers = substr($alltrackers,0,strlen($alltrackers)-1);

	$query = "select * from tracker_properties where sequence='*****'";
	$result = sqlite_query($db, $query);
	$props = sqlite_fetch_array($result,SQLITE_ASSOC);

} else {
	die($sqliteerror);
}

//echo "trackerEditor: db is open";



// these ONLY  control the size and shape of the editin preview window

$height =50;
$width = 160;
$bgcolor = 'white';
$linewidth = 2;
$showmin = '-none-';;
$showmax = '-none-';;
$showlast = '-none-';;
$renderquality = 'high';

//echo "alltrackers=$alltrackers allunits=$allunits ";
$argvals = base64_encode($alltrackers.'|'.$allunits.'|'.
"$height|$width|$linewidth|$bgcolor|$showmin|$showmax|$showlast|$renderquality|$accid");
//echo "trackerEditor: counting trackers";
$count = count($trackers);
//echo "trackerEditor: counted trackers";
$tracker_dictionary = makedictionary($db);
$newtrackerselect = makeselect ($tracker_dictionary);
//echo "trackerEditor: select is built $newtrackerselect";

$html= <<<XXX
<html><head><title>MedCommons - Edit Trackers </title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "gadget.css"; </style>

</head>
<body>
<form method=post action=makeNewTracker.php >
<input type=hidden name=accid value='$accid' />

<table><tr><td><a href="index.html" ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="Edit My Trackers" /></a>
                </td><td>Edit My Trackers <small><i>configure panel for Google,Yahoo</i>
                &nbsp;<a href = 'help.html' target="_help">help</a>
                 </small></td>
</tr></table>
<fieldset>
<legend>
Making and Using Trackers:
</legend>
add new tracker: <select name="tracker">
$newtrackerselect
</select>
<input type=submit value='Go' />
<small>
<br><i>select the trackers you wish displayed in your panel</i>
<br><i>add data values by double clicking on the graphs in your panel</i> 
<br><i>use delete to completely remove a tracker</i>
<br><i>use clear to remove only the data associated with the tracker</i>
</small>
</fieldset>
</form>

<form method=post action=previewTrackers.php >
<input type=hidden name=accid value='$accid' />
<input type = hidden name=argvals value='$argvals' />
<fieldset>
<legend>
All My Trackers:
</legend>
<table class='trackertable'>
XXX;

for ($i=0; $i<$count; $i++)
{
	$displaychecked  = ($display[$i]=='on')?'checked':'';
	$editchecked  = ($edit[$i]=='on')?'checked':'';
	$olderchecked  = ($older[$i]=='on')?'checked':'';
	$args=base64_encode("$trackers[$i]|$units[$i]|$height|$width|$linewidth|$bgcolor|$showmin|$showmax|$showlast|$renderquality|$accid");

	$html.= <<<XXX
<tr><td class='tracker'><a target='_new' href='$units[$i]'>$trackers[$i]</a></td><td>
<img src="$gurl[$i]?accid=$accid&t=$args" alt="$gurl[$i]?accid=$accid&t=$args"/>
</td>
<td class='trackerunit'>
<input type="checkbox" $displaychecked name="cbDisplay$i">
Display this tracker
<br>
<input type="checkbox" $editchecked name="cbEdit$i">
Allow input of new values
<br>
<input type="checkbox" $olderchecked name="cbEditOld$i">
Allow editing of older values
<br>
<input type="radio" name="rbGraphType$i" value="lg"> Line Graph
<input type="radio" name="rbGraphType$i" value="bg"> Bar Graph
</td>
<td>
<input type="submit" name="btDelete$i" value='delete'>
<br>
<input type="submit" name="btClear$i" value='clear'>
</td>
</tr>
XXX;
}

$bc = prop ('background color:','bgcolor',array('white','grey','yellow','red'),$props['bgcolor']);
$sh = prop ('sparkline height:','height',array('12','15','18','20','25','30','40','50','60'),$props['height']);
$sw = prop ('sparkline width:','width',array('120','150','180','210','250','280','310'),$props['width']);
$lw = prop ('line width:','linewidth',array('1','2','3','4','5'),$props['linewidth']);

$showmin = prop('show minimum:','showmin',array("-none-",'red','yellow','blue','green'),$props['showmin']);
$showmax = prop('show maximum:','showmax',array("-none-",'red','yellow','blue','green'),$props['showmax']);
$showlast = prop('show last:','showlast',array("-none-",'red','yellow','blue','green'),$props['showlast']);
$renderquality = prop('image quality:','renderquality',array("normal",'high'),$props['renderquality']);




$html.= <<<XXX
</table>
</fieldset>
<fieldset>
<legend>
Panel properties:
</legend>

<table class='trackertable'>
<tr><td>

<table class='trackertable'>
<tr><td>
$bc
</td></tr><tr><td>
$sh
</td></tr><tr><td>
$sw
</td></tr><tr><td>
$lw
</td></tr>
</table>

</td><td>

<table class='trackertable'>
<tr><td>
$showmin
</td></tr><tr><td>
$showmax
</td></tr><tr><td>
$showlast
</td></tr><tr><td>
$renderquality
</td></tr>
</table>

</td></tr>
</table>
</fieldset>
<br>
<input type="submit" name = "btPreview" value= "preview" >
<input type="submit" name="btSave" value="save">
</form>
</body>
</html>
XXX;

echo $html;
?>
