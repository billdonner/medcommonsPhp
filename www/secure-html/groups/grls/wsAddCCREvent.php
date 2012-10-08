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
	
	require_once "args.inc.php";
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
 
$insert=
"INSERT INTO groupccrevents (groupinstanceid,PatientGivenName, PatientFamilyName, PatientIdentifier, PatientIdentifierSource, Guid, Purpose, 
		SenderProviderId, ReceiverProviderId, DOB, CXPServerURL, CXPServerVendor, ViewerURL,Comment,
		CreationDateTime, ConfirmationCode, RegistrySecret, PatientSex, PatientAge,Status) 
   VALUES ('$gid','$pgn', '$pfn', '$pid', '$pis', '$guid', '$purp',
 	    	'$spid', '$rpid', '$dob', '$cxpserv', '$cxpvendor','$viewerurl', '$comment',
             '$timenow', '$cc', '$rs', '$psx', '$pag','New');";
			
$this->dbexec($insert,"can not insert into table groupccrevents/$gid - ");
			$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
		} 
}
//main
$x = new addCCREventWs();
$x->handlews("addCCREvent_Response");
?>
