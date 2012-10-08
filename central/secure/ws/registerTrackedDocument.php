<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
// upgraded 11/21/05 to take optional ekey
class registerTrackedDocumentWs extends dbrestws {
	// variant of registerTrackDocument with trackingNumber supplied

	
	function xmlbody(){
		
		$this->gethostarg();
		// pick up and clean out inputs from the incoming args
		$mcid =$this->cleanreq('mcid');
		$guid = $this->cleanreq('guid');
		$rights = $this->cleanreq('rights');
		$ekey = $this->cleanreq('ekey');		
		$intstatus = $this->cleanreq('intstatus');
		$pinHash = $this->cleanreq('pinHash');
		$tracking = $this->cleanreq('trackingNumber'); 
		
		
		
		// if medcommonsid is zeroes for POPS, then generate an mcid and tracking number such that 
		// mcid = tracking number with special code prepended
		
		if ($mcid=="0000000000000000") $mcid = "0000".$tracking; // 16 digit mcid
		
		//
		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",	
		$this->xmfield("mcid",$mcid).
		$this->xmfield("rights",$rights).
		$this->xmfield("pinHash",$pinHash).	
		$this->xmfield("ekey",$ekey).	
		$this->xmfield("trackingNumber",$tracking).			
		$this->xmfield("guid",$guid)));
		
		// wld 102105 check to make sure there is a record reserved here
		
		
		
		$query = "SELECT * from tracking_number WHERE (tracking_number = '$tracking' AND 
														encrypted_pin='999999999999' )";
		$result = $this->dbexec($query,"can not select from tracking_table in registerTrackedDocument - ");
		$rows = mysql_num_rows($result);
		if ($rows != 1) $this->xmlend("Can not find pre-allocated tracking number in registerTrackedDocument");
		
		//
		// add to the document table

		$timenow=time();
		$insert="INSERT INTO document (guid,creation_time) ".
		"VALUES('$guid',NOW())";
		$this->dbexec($insert,"can not insert into table document - ");
		//
		//pick up the id we just created
		$docid = mysql_insert_id();
		
		// put an entry in the document location table
		//$locid = $this->adddocumentlocation($docid,$this->getnodeid(),$ekey,$intstatus);
		//
		// add to the rights table
		//
		$insert="INSERT INTO rights (user_medcommons_user_id,document_ID,rights,creation_time) ".
													"VALUES('$mcid','$docid','$rights',NOW())";
		$this->dbexec($insert,"can not insert into table rights - ");
		$rightsid = mysql_insert_id();
		
		// add to the tracking_number table

		$update = "UPDATE tracking_number SET encrypted_pin='$pinHash', rights_id='$rightsid'
											WHERE (tracking_number = '$tracking')";			
					
		$this->dbexec($update,"can not update into table tracking_number from registerTrackDocument - ");
		
		
		// return outputs
		//docid,rightsid,mcid
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("docid",$docid).	
		$this->xmfield("rightsid",$rightsid).
		$this->xmfield("mcid",$mcid).

		$this->xmfield("status","ok")));
	}
}

//main

$x = new registerTrackedDocumentWs();
$x->handlews("registerTrackedDocument_Response");



?>