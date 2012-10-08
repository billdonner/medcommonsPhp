<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class trackDocumentWs extends dbrestws {


	function xmlbody(){
		// pick up and clean incoming arguments
		$guid=$this->cleanreq('guid');
		$mcid=$this->cleanreq('mcid');

		$pinHash=$this->cleanreq('pinHash');

		//
		// echo inputs
		//
		$this->xm($this->xmfield ("inputs",
		$this->xmfield("guid",$guid).		$this->xmfield("mcid",$mcid).
		$this->xmfield("pinHash",$pinHash)));
		//
		// find document id from document table by guid
		//
		
		$query = "SELECT * from document WHERE (guid='$guid')";
		$result = $this->dbexec($query,"can not select from table document - ");
		if ($result===false) return ($this->xmfield("document", $result));
		
		$l = mysql_fetch_object($result);
		$docid = $l->id; 
		//
		// create a rights block, and get its id
		$rights = 1234; 
		//
		// make up a tracking number, and a corresponding medcommons id if needed
		$tracking = $this->generate_tracking();
		
		// wld 102105 if we didn't get a good tracking nmber then return immediately
		if ($tracking=='') $this->xmlend("internal error in wstrackDocument - could not allocate tracking number");
		
		if ($mcid=="" || $mcid=="0000000000000000")$mcid = "0000".$tracking;
	
		// add to the rights table
		//
		$insert="INSERT INTO rights (user_medcommons_user_id,document_ID,rights,creation_time) ".
													"VALUES('$mcid','$docid','$rights',NOW())";
		$this->dbexec($insert,"can not insert into table rights - ");
		$rightsid = mysql_insert_id();
		
		// there is some circularity here, since the rights id is needed for the tracking_number table
		// and the tracking_number is part of the mcid which is needed for the rights table
		
		// add to the tracking_number table

		$update = "UPDATE tracking_number SET encrypted_pin='$pinHash', rights_id='$rightsid'
											WHERE (tracking_number = '$tracking')";			
					
		$this->dbexec($update,"can not  update into table tracking_number from wstrackDocument - ");
		
		//
		// return outputs
		//
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("trackingNumber",$tracking).
		$this->xmfield("docid",$docid).
		$this->xmfield("mcid",$mcid).
		$this->xmfield("status","ok")));
	}
}

//main

$x = new trackDocumentWs();
$x->handlews("trackDocument_Response");
?>