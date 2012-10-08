<?php

require_once "alib.inc.php";

// revised the meaning of rolehack, it is now holding a little language that is interpreted to direct the selection and
// assemblage of the page to "Start" the logged on user

// this page must be wired into the mcidentity/servers table in slot 1 for it to be transitioned to automagically on login



function showStart()
{
	
	$out='';
// make sure the user is logged on and get the rolehack field;
list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not lo

$db = aconnect_db(); // connect to the right database

$query = "Select rolehack from users WHERE (mcid = '$accid')";
$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
$f = mysql_fetch_assoc($result);
$role=trim($f['rolehack']);// get whatever is there
if ($role=='')$role='ccr|rls|adm|full';
$commands = explode('|',$role);
$url = false; //not found yet
$firsturl = false;// also remember the first
$out.= "<br>The Startup Script associated with this MedCommons account is $role";
foreach ($commands as $command)
{
	// interpret each command and try and do what it says
	// the target routines might actually switch away and never return
	$out .= " <br>&nbsp;&nbsp;testing <b>$command</b>?&nbsp; ";
	if (substr($command,0,5)=='goto:'){
		$out .= "succeeds"; 
		$url=substr($command,5);
	}
	else
	switch ($command)
	{
		case 'rls': { $url=tryRls($accid);
		if ($url===false) $out .= "fails"; else $out .= "succeeds"; break;}
		case 'ccr': { $url=tryCCR($accid);
		if ($url===false) $out .= "fails"; else $out .= "succeeds"; break;}
		case 'adm': { $url=tryPracticeAdmin($accid);
		if ($url===false) $out .= "fails"; else $out .= "succeeds"; break;}
		case 'full': { $url=tryFullPage();
		if ($url===false) $out .= "fails"; else $out .= "succeeds"; break;}
		case 'none': { $url=tryNoStart();
		if ($url===false) $out .= "fails"; else $out .= "succeeds"; break;}
		case 'topics':{ $out.= "shows topics"; break;}
		default:  { $url=false; break;}// if unknown, just skip

	}
	if ($firsturl === false) {$firsturl=$url;$firstcommand=$command;}

}
// if here with nothing, just tryNoStart

if ($url===false) $url = tryNoStart();

// finally, just go there - we can do a better job for those pages that are part of the /acct service and just
// 'require' them, but holding off until we have more debugging

$out .= "<br>At this point in time, upon next logon the user will execute the command <b>$firstcommand</b> and goto 
<b>$firsturl</b>&nbsp;<small><a target='_top' href=$firsturl>try it</a></small>";

return $out;

}

?>