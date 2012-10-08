<?php
require_once "wslibdb.inc.php";

class queryRLSWs extends dbrestws {


	function xmlbody(){

		require_once "args.inc.php";
		require_once "where.inc.php";

		$limit = cleanreq('limit');if($limit=='') $limit=20; if ($limit>20) $limit=20;

		$timenow=time();	// unix style integer timestamp
		$this->xm($this->xmfield ("inputs",
			$this->xmfield("gid","$gid").
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


    $select= "SELECT * FROM groupccrevents $whereclause ORDER BY CreationDateTime DESC";
    if($limit > 0) $select.=" LIMIT $limit";

		$result = $this->dbexec($select,"can not select from  table groupccrevents - $select");
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
			$this->xmfield("ViewerUrl",$l['ViewerUrl']).
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
