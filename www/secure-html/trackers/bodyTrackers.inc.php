<?php
// just builds the <body> section of the trackers view, so that google gadgets are happy
require_once 'dblocation.inc.php';

function tconfirm_logged_in()
{
	if (!isset($_COOKIE['mc'])) return false;
	$mc = $_COOKIE['mc'];
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	if ($mc!='')
	{
		$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
		$props = explode(',',$mc);
		for ($i=0; $i<count($props); $i++) {
			list($prop,$val)= explode('=',$props[$i]);
			switch($prop)
			{
				case 'mcid': $accid=$val; break;
				case 'fn': $fn = $val; break;
				case 'ln': $ln = $val; break;
				case 'email'; $email = $val; break;
				case 'from'; $idp = stripslashes($val); break;
			}
		}
	}
	return array($accid,$fn,$ln,$email,$idp,'');
}
function bodyTrackers($accid,$dir,$inaccount, $editor,$input,$help)
{
	if ($editor!='') $editortarget = "target='$editor'";else $editortarget='';
	if ($input!='') $inputtarget = "target='$input'";else $inputtarget='';
	if ($help!='') $helptarget = "target='$help'";else $helptarget='';

	$time = time();
	$__trackerdb = get_tracker_db($accid);
	if (!file_exists($__trackerdb)) {
		// file doesn't exist, so make it
		require_once 'createtrackerdb.inc.php';
	}
	if ($db = sqlite_open($__trackerdb, 0666, $sqliteerror))
	{
		$query = 'select * from tracker_properties';
		$result = sqlite_query($db, $query);
		$props = sqlite_fetch_array($result,SQLITE_ASSOC);

		$query = 'select * from tracker_info';
		$result = sqlite_query($db, $query);
		$count = 0;
		while (true) {
			$l = sqlite_fetch_array($result,SQLITE_ASSOC);
			if ($l===false) break;
			$trackers[$count]=$l['tracker'];
			$units[$count]=$l['units'];
			$display[$count]=$l['show_display'];
			$edit[$count]=$l['allow_input'];
			$older[$count]=$l['edit_older'];
			$value[$count]=$l['value'];
			$gurl[$count]=$l['graphicurl'];
			$ginputurl[$count]=$l['ginputurl'];
			$count++;
		}
	}
	else {
		die($sqliteerror);
	}

	/*
	// start here
	*/
	// preview
	$self=$_SERVER['PHP_SELF'];//for refresh
	if (!$inaccount) $refresh = "<a href = '$self' > refresh </a>"; else $refresh='';
	$html= <<<XXX
<body>
<table><tr><td><a href="http://www.medcommons.net/index.html" target='_new'><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="My Trackers" /></a>
                </td><td><small><i>click to input</i>$refresh
                &nbsp;<a href = '$dir/trackerEditor.php?accid=$accid' $editortarget >edit</a>
                &nbsp;<a href = '$dir/help.html' $helptarget >help</a></small></td></tr>
                </table><table class='trackertable'>
XXX;
	if ($count==0) $html.="<p>There are no Trackers Defined</p>
<p><a href = '$dir/trackerEditor.php?accid=$accid' $editortarget >create Trackers</a></p>"; 
	else
	for ($i=0; $i<$count; $i++)
	{
		if ($edit[$i]=='on'){
			$prefix = "<a  href='$ginputurl[$i]?accid=$accid&tracker=$trackers[$i]' $inputtarget >";
			$postfix = "</a>";
		} else
		$prefix = $postfix = '';

		if ($display[$i]=='on'){
			$uargs=("$trackers[$i]|$units[$i]|".$props['height'].'|'.$props['width'].'|'.$props['linewidth']
			.'|'.$props['bgcolor'].'|'.$props['showmin'].'|'.$props['showmax'].'|'.$props['showlast'].'|'.$props['renderquality'].'|'.$accid);
			$args = base64_encode($uargs);
			$html.= <<<XXX
<tr><td class='tracker' align='right'><span class='newvalue'>$value[$i]</span>&nbsp;<a target='_new' href='$units[$i]'>$trackers[$i]</a></td>
<td>$prefix
<img src="$gurl[$i]?accid=$accid&t=$args" alt="$gurl[$i]?accid=$accid"/>
$postfix</td></tr>
XXX;
}
}
$html.= <<<XXX
</table>
</body>
XXX;
// make unorthodox call
//require_once "../acctlib.inc.php";
//addAppEvent('$accid','234323434','PayPerView','1399');

return $html;
}
?>