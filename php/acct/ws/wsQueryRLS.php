<?php
require_once "wslibdb.inc.php";

class queryRLSWs extends dbrestws {


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
$pcid = cleanreq('pid');  // note this is the practice id


// build WHERE clause for select statement based on the arguments
// added $pcid multiplexing on groupid
$where = "WHERE practiceid = '$pcid' and ViewStatus='Visible'"; $wc = 1;
if ($pfn!='') $where.= (($wc++ != 0)?" AND ":"" )."PatientFamilyName LIKE  '$pfn' ";
if ($pgn!='') $where.= (($wc++ != 0)?" AND ":"" )."PatientGivenName LIKE '$pgn' ";
if ($pid!='') $where.= (($wc++ != 0)?" AND ":"" )."'$pid'=PatientIdentifier";
if ($pis!='') $where.= (($wc++ != 0)?" AND ":"" )."'$pis'=PatientIdentifierSource";
if ($cc!='')  $where.= (($wc++ != 0)?" AND ":"" )."'$cc'=ConfirmationCode";
if ($dob!='') $where.= (($wc++ != 0)?" AND ":"" )."'$dob'=DOB";
if ($spid!='') 
             $where.= (($wc++ != 0)?" AND ":"" )."'$spid'=SenderProviderId";
if ($rpid!='') 
			 $where.= (($wc++ != 0)?" AND ":"" )."'$rpid'=ReceiverProviderId";

if ($wc!=0) $whereclause = $where; else $whereclause='';
		$limit = cleanreq('limit');if($limit=='') $limit=20; if ($limit>20) $limit=20;

		$timenow=time();	// unix style integer timestamp
		$this->xm($this->xmfield ("inputs",
			$this->xmfield("gid","$pcid").
		$this->xmfield("PatientFamilyName","$pfn").
		$this->xmfield("PatientGivenName","$pgn").
		$this->xmfield("PatientIdentifier","$pid").
		$this->xmfield("PatientIdentifierSource","$pis").
		$this->xmfield("SenderProviderId","$spid").
		$this->xmfield("ReceiverProviderId","$rpid").
		$this->xmfield("DOB","$dob").
		$this->xmfield("ConfirmationCode","$cc").
		$this ->xmfield ("where","$where").
		$this->xmfield("timenow","$timenow")
		));


    $select= "SELECT * FROM practiceccrevents $whereclause ORDER BY CreationDateTime DESC";
    if($limit > 0) $select.=" LIMIT $limit";

		$result = $this->dbexec($select,"can not select from  table practiceccrevents - $select");
		$rows = mysql_numrows($result);
		if ($rows == 0)
		$this->xm($this->xmfield ("outputs",$this->xmfield("status","failed 0 rows returned")));
		else
		{//rows
		$this->xm("<outputs>".$this->xmfield("status","ok rows=$rows limit=$limit"));
		while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$this->xm($this->xmfield ("RLSentry",
			$this->xmfield("PatientFamilyName",$l['PatientFamilyName']).
			$this->xmfield("PatientGivenName",$l['PatientGivenName']).
			$this->xmfield("PatientSex",$l['PatientSex']).
			$this->xmfield("PatientIdentifier",$l['PatientIdentifier']).
			$this->xmfield("PatientIdentifierSource",$l['PatientIdentifierSource']).
			$this->xmfield("DOB",$l['DOB']).
			$this->xmfield("PatientAge",$l['PatientAge']).
			$this->xmfield("Guid",$l['Guid']).
			$this->xmfield("Purpose",$l['Purpose']).
			$this->xmfield("CXPServerURL",$l['CXPServerURL']).
			$this->xmfield("CXPServerVendor",$l['CXPServerVendor']).
			$this->xmfield("ViewerUrl",$l['ViewerURL']).
			$this->xmfield("Comment",$l['Comment']).
			$this->xmfield("ConfirmationCode",$l['ConfirmationCode']).
			$this->xmfield("RegistrySecret",$l['RegistrySecret']).
			$this->xmfield("CreationDateTime",$l['CreationDateTime']).
			$this->xmfield("SenderProviderId",$l['SenderProviderId']).
			$this->xmfield("ReceiverProviderId",$l['ReceiverProviderId']).
			$this->xmfield("Status",$l['Status'])
			));
		} ; // end of while
		$this->xm("</outputs>");
		} // end of have rows
	}
}

//main

$x = new queryRLSWs();
$x->handlews("queryRLS_Response");
?>
