<?php



function aprettyguid($guid)
{
	$size = strlen($guid);
	return ( substr($guid,0,4)."..".substr($guid,$size-4,4));
}

function aprettytrack($track) {
	$out = "";
	if($track) {
		$out = substr($track,0,4)." ".substr($track,4,4)." ".substr($track,8,4);
	}
	return $out;
}

function aprettyaccid($accid) {
	$out = "";
	if($accid) {
		$out = substr($accid,0,4)." ".substr($accid,4,4)." ".substr($accid,8,4)." ".substr($accid,12,4);
	}
	return $out;
}
function ccrlog_row($r) 
{
	$guid = $r->guid;
	$tracking = $r->tracking;
	$prettytrack=aprettytrack($tracking);
	$to = $r->dest; // found by phplint
	$subject = $r->subject;
	$id = $r->id;
	$curl =$GLOBALS['Commons_Url'].'gwredirguid.php' ;
	// This version asks for PIN
	// $href = $curl."?guid=$guid&tracking=$tracking&free=$free";
	// This version won't ask for PIN
	$href =$GLOBALS['Commons_Url']."gwredirguid.php?guid=$guid&tracking=$tracking&free=true";


	$whereavailable = "only to the patient";
	if ($r->idp!='') $whereavailable = "to the patient and provider ".$r->idp;

	if($r->status == "RED") {
		$rowclass="class='emergencyccr' title='this ccr will be offered on the back of your healthcare card for emergency use'";
		$redlink ="<a title='This is your emergency CCR, click here to remove' onclick='return clearccrpressed(\"$id\");'><img class='clickable' src='images/RedCross_16.gif' /></a>";
	}
	else {
		$rowclass=" title = 'this ccr is available $whereavailable'";
		$redlink="";
	}

	$out= "
          <tr $rowclass>
            <td>$r->accid&nbsp;</td>
                <td class='tndate'>$r->date&nbsp;</td>
                <td class='tncell'>&nbsp;<a target='_new' href='$href'>$prettytrack</a>&nbsp;</td>
                ";

		$out.= "<td>$to</td><td>$subject</td></tr>";
	
	
	return $out;
}

function auditlog ($gid,$limit,$filter)
{
// builds either a full system log ($gid==0) or a group level log

// the algorith for inclusion
 
 list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in();
//build menu to present from arg
// get settings for how to behave
$db = aconnect_db(); // connect to the right database
if ($filter=='FAX') $filter="and subject like 'FAX Notification'"; else $filter="and subject not like 'FAX Notification'";
if ($gid =='') $q="select *, DATE_FORMAT(date, '%c/%d/%Y %H:%i') as prettydate from ccrlog"; else 
$q = "SELECT *,DATE_FORMAT(date, '%c/%d/%Y %H:%i') as prettydate from ccrlog,groupmembers where memberaccid=accid and groupinstanceid = '$gid' $filter";
$q.= " order by date DESC LIMIT $limit";

$result = mysql_query($q) or die ("can not query $q ".mysql_error());

//echo " rows =". mysql_numrows($result);

$out= "<table class='ccrtable ' cellspacing='0' cellpadding='0'>";
		$miniview = false;
		$showProviders = true;


	while (true) {
		$l = mysql_fetch_object($result);
		if ($l===false) break;
		$l->date = $l->prettydate; // fool the code a bit
		$out.=ccrlog_row($l);
	}

$out.= "</table>";
return $out;
}
?>