<?php

require_once "alib.inc.php";

// revised the meaning of rolehack, it is now holding a little language that is interpreted to direct the selection and
// assemblage of the page to "Start" the logged on user

// this page must be wired into the mcidentity/servers table in slot 1 for it to be transitioned to automagically on login

function tryCCR ($accid) {

	// this is a gruesome query to find the newest CCR and then to go there if we have one
  // ssadedin: not too sure what this query is doing .... see tryCCCR()
	$query = "SELECT guid
              from ccrlog 
              left join document_type d on dt_account_id = accid  and ((dt_tracking_number = tracking) or (dt_guid = guid))
              where (accid = '$accid') and (status <> 'DELETED') LIMIT 1;";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$obj = mysql_fetch_object($result);
	mysql_free_result($result);
	if ($obj===false) return false;
	return $GLOBALS['Commons_Url']."gwredirguid.php?guid=".$obj->guid;
}
function tryRls ($accid) {

	// see if we are a member of any practice before going to providerPage where the RLS will be brought up

	$query = "SELECT * from practice q, groupmembers p, groupinstances i , users u
			where p.memberaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
			i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
			p.memberaccid=u.mcid order by q.practicename LIMIT 1";
	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	mysql_free_result($result);
	//		echo "in tryRLS rowcount is $rowcount";
	if ($rowcount==0) return false;

	return $GLOBALS['Accounts_Url']."providerPage.php";
}

function tryPracticeAdmin ($accid) {

	// see if we are an administrator of any practices before going to the practiceadmin page
	$query = "SELECT * from practice q, groupadmins p, groupinstances i , users u
		where p.adminaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
		i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
		p.adminaccid=u.mcid order by q.practicename ";
	$result = mysql_query ($query) or die("can not query table groupadmins - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	mysql_free_result($result);
	//		echo "in tryPracticeAdmin rowcount is $rowcount";
	if ($rowcount==0) return false;
	return $GLOBALS['Accounts_Url']."flatPageAdmin.php";

}
function tryFullPage () {return $GLOBALS['Accounts_Url']."flatPageFull.php";}
function tryNoStart () {return $GLOBALS['Accounts_Url']."noStart.php";}


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
foreach ($commands as $command)
{
	// interpret each command and try and do what it says
	// the target routines might actually switch away and never return

	switch ($command)
	{
		case 'rls': { $url=tryRls($accid); break;}
		case 'ccr': { $url=tryCCR($accid); break;}
		case 'adm': { $url=tryPracticeAdmin($accid); break;}
		case 'full': { $url=tryFullPage(); break;}
		case 'none': { $url=tryNoStart(); break;}
		default:  { $url=false; break;}// if unknown, just skip

	}
	if ($url!==false)break; // if we have found something then stop executing

}
// if here with nothing, just tryNoStart

if ($url===false) $url = tryNoStart();

// finally, just go there - we can do a better job for those pages that are part of the /acct service and just
// 'require' them, but holding off until we have more debugging

$html=<<<XXX
<html><head><title>redirecting to $command $url ($accid)</title></head>
<body onLoad="document.theform.submit();">
<form target="_top" name='theform' action='$url' method='post'>
</form>
redirecting to $command $url ($accid).....
</body></html>
XXX;
echo $html;
?>
