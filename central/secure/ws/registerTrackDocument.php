<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class registerTrackDocumentWs extends dbrestws {
	
	function xmlbody(){
		$this->gethostarg();
		// pick up and clean out inputs from the incoming args
		$mcid =$this->cleanreq('mcid');
		$guid = $this->cleanreq('guid');
		$ekey = $this->cleanreq('ekey');

		$rights = $this->cleanreq('rights');
		$pinHash = $this->cleanreq('pinHash');
		
		$tracking = $this->generate_tracking(); // pick one out of thin air
		
		
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
		$this->xmfield("intstatus",$intstatus).
		$this->xmfield("guid",$guid)));
		
		// wld 102105 if we didn't get a good tracking nmber then return immediately
		if ($tracking=='') $this->xmlend("internal error in registerTrackDocument - could not allocate tracking number");

    // Only create the document if it is not already there    
    $docidResult = $this->dbexec("select id from document where guid = '$guid'",
            "internal error in registerTrackDocument - could not select from document table");
    if(mysql_num_rows($docidResult) == 0) {
      // add to the document table
      $timenow=time();
      $insert="INSERT INTO document (guid,creation_time) ".
      "VALUES('$guid',NOW())";
      $this->dbexec($insert,"can not insert into table document - ");
      //
      //pick up the id we just created
      $docid = mysql_insert_id();
   }
   else {
     $docidRow = mysql_fetch_row($docidResult);
     $docid = $docidRow[0];
   }

		// put an entry in the document location table
		//$locid = $this->adddocumentlocation($docid,$this->getnodeid(),$ekey,$intstatus);
		//
		// add to the rights table
		//
		$insert="INSERT INTO rights (user_medcommons_user_id,document_ID,rights,creation_time) ".
													"VALUES('$mcid','$docid','$rights',NOW())";
		$this->dbexec($insert,"can not insert into table rights - ");
		$rightsid = mysql_insert_id();
		//
		// insert the pin and rights id, it should be in the slot we just allocated above
		//
		$update = "UPDATE tracking_number SET encrypted_pin='$pinHash', rights_id='$rightsid'
											WHERE (tracking_number = '$tracking' AND 
		                                           encrypted_pin = '999999999999')";			
					
		$this->dbexec($update,"can not update into table tracking_number from registerTrackDocument - ");
		
		
		// return outputs
		//docid,rightsid,mcid
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("trackingNumber",$tracking).
		$this->xmfield("docid",$docid).
		$this->xmfield("locid",$locid).
		$this->xmfield("rightsid",$rightsid).
		$this->xmfield("mcid",$mcid).

		$this->xmfield("status","ok")));
	}
}

//main

$x = new registerTrackDocumentWs();
$x->handlews("registerTrackDocument_Response");



?>
