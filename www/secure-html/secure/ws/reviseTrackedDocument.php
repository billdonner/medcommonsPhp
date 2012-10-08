<?php
require_once "../ws/wslibdb.inc.php";
/**
 * reviseTrackedDocumentWs 
 *
 * Updates a specified tracking number with a new guid.
 *
 * Inputs:
 *    trackingNumber - tracking number to update
 *    pinHash - hashed form of pin for specified tracking number
 *    guid - new guid for specified tracking number
 */
class reviseTrackedDocumentWs extends dbrestws {
	// variant of registerTrackDocument with trackingNumber supplied

	
	function xmlbody(){
		
		$this->gethostarg();
		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$rights = $this->cleanreq('rights');
		$ekey = $this->cleanreq('ekey');		
		$intstatus = $this->cleanreq('intstatus');
		$pinHash = $this->cleanreq('pinHash');
		$tracking = $this->cleanreq('trackingNumber'); 
		
		//
		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",	
		$this->xmfield("rights",$rights).
		$this->xmfield("pinHash",$pinHash).	
		$this->xmfield("ekey",$ekey).	
		$this->xmfield("trackingNumber",$tracking).			
		$this->xmfield("guid",$guid)));
		
		// check to make sure the entry is correct
		$query = "SELECT * from tracking_number WHERE tracking_number = '$tracking' AND (encrypted_pin='$pinHash' OR encrypted_pin='999999999999')";

		$result = $this->dbexec($query,"can not select from tracking_table in reviseTrackedDocument - ");
		$rows = mysql_num_rows($result);
		if ($rows != 1) $this->xmlend("Can not find requested tracking number with given credentials"); 

    $trackingNumber = mysql_fetch_object($result);
    $mcid = $trackingNumber->account_id;
    
    // Find the correct document id
    $query = "select id from document where guid = '$guid'";
		$result = $this->dbexec($query,"requested document not found in reviseTrackedDocument - ");
    $documentRow = mysql_fetch_row($result);
    if(!$documentRow) {
      $this->xmlend("no rows found for requested document in reviseTrackedDocument - ");
    }
    $docid = $documentRow[0];

    // Update the rights table to point at the new document
    $update = "update rights set document_id = $docid where rights_id = $trackingNumber->rights_id";
		$result = $this->dbexec($update,"cannot update rights table with new guid");

    // Update encrypted pin (only necessary for case where it is 99999
    if($trackingNumber->encrypted_pin == "999999999999") {
      $update = "update tracking_number set encrypted_pin = '$pinHash' where tracking_number = '$trackingNumber'";
      $result = $this->dbexec($update,"cannot update tracking number table with new encrypted pin");
    }
		
		// return outputs
		// docid,rightsid,mcid
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("docid",$docid).		
    $this->xmfield("rightsid",$trackingNumber->rights_id).
		$this->xmfield("mcid",$mcid).

		$this->xmfield("status","ok")));
	}
}

//main
$x = new reviseTrackedDocumentWs();
$x->handlews("reviseTrackedDocument_Response");

?>
