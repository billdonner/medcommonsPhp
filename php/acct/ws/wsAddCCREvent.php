<?php
require_once "wslibdb.inc.php";
function parse($s,$tag)
{
// returns whatever follows after tag, upto a blank or tab or eos
$pos = strpos($s,$tag);
if ($pos===false) return '';
// alright, found it, look for next blank or tab
$pos2 = strpos($s," ",$pos); 
if ($pos2===false) $pos2=strpos($s,"	",$pos);//try again with tabs
if ($pos2===false) $len = strlen($s) - strlen($tag); else $len = $pos2-$pos+1-strlen($tag);
// 
return substr($s,$pos+strlen($tag),$len);
}
class addCCREventWs extends dbrestws {
	function xmlbody(){
	
function cleanreq ($x) { if (isset($_REQUEST[$x])) return $_REQUEST[$x]; else return false;}
// these are the basic query parameter arguments that are passed around
//
//
$pfn = cleanreq('PatientFamilyName');
$pgn = cleanreq('PatientGivenName');
$pid= cleanreq('PatientIdentifier');
$pis= cleanreq('PatientIdentifierSource');
$psx = cleanreq('PatientSex');
$pag = cleanreq('PatientAge');
$spid  = cleanreq('SenderProviderId');
$rpid  = cleanreq('ReceiverProviderId');
$dob  = cleanreq('DOB');
$cc = cleanreq('ConfirmationCode');
// these are not query parameters, but are passed around
$rs = cleanreq('RegistrySecret');
$guid = cleanreq('Guid');
$purp = cleanreq('Purpose');
$cxpserv = cleanreq('CXPServerURL');
$cxpvendor = cleanreq('CXPServerVendor');
$viewerurl = cleanreq('ViewerURL');
$comment = cleanreq('Comment');
// these params control the formatting of output
$int = cleanreq('int'); // if non-zero, ajax'd dynamic updates
$st = cleanreq('st');
$ti = cleanreq('ti');
$limit = cleanreq('limit');
$logo = cleanreq('logo');
// this multiplexes the group - wld 072506
$gid = cleanreq('pid');

		$timenow=time();	// unix style integer timestamp
		$this->xm($this->xmfield ("inputs",
			$this->xmfield("gid","$gid"). //wld group multiplexing
		$this->xmfield("pfn","$pfn").
		$this->xmfield("pgn","$pgn").
		$this->xmfield("pid","$pid").
		$this->xmfield("pis","$pis").
		$this->xmfield("psx","$psx").
		$this->xmfield("pag","$pag").
		$this->xmfield("guid","$guid").
		$this->xmfield("purp","$purp").
		$this->xmfield("spid","$spid").
		$this->xmfield("rpid","$rpid").
		$this->xmfield("dob","$dob").
		$this->xmfield("cxpserv","$cxpserv").
		$this->xmfield("cxpvendor","$cxpvendor").
		$this->xmfield("viewerurl","$viewerurl").		
		$this->xmfield("comment","$comment").
		$this->xmfield("cc","$cc").
		$this->xmfield("rs","$rs")
		));


 // parse out the comment field and override
$x = parse($comment,'pfn:'); if ($x!='') $pfn = $x;
$x = parse($comment,'pgn:'); if ($x!='') $pgn = $x;
$x = parse($comment,'pid:'); if ($x!='') $pid = $x;
$x = parse($comment,'pis:'); if ($x!='') $pis = $x;
$x = parse($comment,'sprid:'); if ($x!='') $spid = $x;
$x = parse($comment,'rprid:'); if ($x!='') $rpid = $x;
// $providerparsed = parse($ns,'provider:');

// Update 
if($pid && ($pid!="")) {
  $this->dbexec(
    "update practiceccrevents set ViewStatus = null
     where PatientIdentifier='$pid' and practiceid='$gid'",
    "can not update table practiceccrevents/$gid - ");
}

$result = $this->dbexec("select value from mcproperties where property = 'acAccountStatus'","cannot select from mcproperties");
$row = mysql_fetch_array($result);
$statusValues = explode(',',$row ? $row[0] : "");
$defaultStatus = mysql_real_escape_string((count($statusValues)>0) ? $statusValues[0] : "");
 
$insert=
"INSERT INTO practiceccrevents (practiceid,PatientGivenName, PatientFamilyName, PatientIdentifier, PatientIdentifierSource, Guid, Purpose, 
		SenderProviderId, ReceiverProviderId, DOB, CXPServerURL, CXPServerVendor, ViewerURL,Comment,
		CreationDateTime, ConfirmationCode, RegistrySecret, PatientSex, PatientAge,Status,ViewStatus) 
   VALUES ('$gid','$pgn', '$pfn', '$pid', '$pis', '$guid', '$purp',
 	    	'$spid', '$rpid', '$dob', '$cxpserv', '$cxpvendor','$viewerurl', '$comment',
             '$timenow', '$cc', '$rs', '$psx', '$pag','$defaultStatus','Visible');";
			
$this->dbexec($insert,"can not insert into table practiceccrevents/$gid - ");
			$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
		} 
}
//main
$x = new addCCREventWs();
$x->handlews("addCCREvent_Response");
?>
