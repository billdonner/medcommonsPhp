<?php

require_once "alib.inc.php";

// revised the meaning of rolehack, it is now holding a little language that is interpreted to direct the selection and
// assemblage of the page to "Start" the logged on user

// this page must be wired into the mcidentity/servers table in slot 1 for it to be transitioned to automagically on login




// make sure the user is logged on and get the rolehack field;
list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not lo

$db = aconnect_db(); // connect to the right database

$query = "Select rolehack from users WHERE (mcid = '$accid')";
$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
$f = mysql_fetch_assoc($result);
$role=trim($f['rolehack']);// get whatever is there
if (substr($role,0,5)!='goto:') 
{ //interpret pipe commands
if ($role=='')$role='ccr|rls|adm|full';
$commands = explode('|',$role);
$url = false; //not found yet
foreach ($commands as $command)
{
	// interpret each command and try and do what it says
	// the target routines might actually switch away and never return

	switch ($command)
	{
		case 'hm': { $url=$GLOBALS['Homepage_Url'].'/info.php'; break;}
		case 'hmwl': { $url=$GLOBALS['Homepage_Url'].'/info.php?expand=worklist'; break;}
		case 'rls': { $url=tryRls($accid); break;}
		case 'ccr': { $url=tryCCCR($accid); break;}
		case 'adm': { $url=tryPracticeAdmin($accid); break;}
		case 'full': { $url=tryFullPage(); break;}
		case 'none': { $url=tryNoStart(); break;}
		case 'topics': {$url=$GLOBALS['Homepage_Url'].'/interests.html'; break;}
		
		default:  { $url=false; break;}// if unknown, just skip

	}
	if ($url!==false)break; // if we have found something then stop executing

}
// if here with nothing, just tryNoStart

if ($url===false) $url = tryNoStart();

}
else 
{
	// was a direct goto:, just pick up the rest and go there, should be relative to current location in /acct
	$url = trim(substr($role,5)); // pick up rest
	$command = 'goto:';
}

// finally, just go there - we can do a better job for those pages that are part of the /acct service and just
// 'require' them, but holding off until we have more debugging

$html=<<<XXX
<html><head>
  <title>Redirecting to $command $url ($accid)</title>
  <script type="text/javascript" src="utils.js"/>
  <script type="text/javascript">setCookie('mctz',new Date().getTimeZoneOffset());</script>
  <style type="text/css">
    * { 
      font-family: arial;
    }
  </style>
</head>
<body onLoad="document.theform.submit();">
<form target="_top" name='theform' action='$url' method='post'>
</form>
redirecting to $command $url ($accid).....
</body></html>
XXX;
echo $html;
?>
