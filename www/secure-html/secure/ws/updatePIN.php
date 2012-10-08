<?php
require_once "../ws/wslibdb.inc.php";
/**
 * updatePINWs 
 *
 * Updates a specified tracking number with a new guid.
 *
 * Inputs:
 *    trackingNumber - tracking number to update
 *    pinHash - hashed form of pin for specified tracking number
 *    guid - new guid for specified tracking number
 */
class updatePINWs extends dbrestws {
	// variant of registerTrackDocument with trackingNumber supplied

	
	function xmlbody(){
		
		//$this->gethostarg();

		// pick up and clean out inputs from the incoming args
		$oldPinHash = $this->cleanreq('oldPINHash');
		$newPinHash = $this->cleanreq('newPINHash');
		$tracking = $this->cleanreq('trackingNumber'); 
		
		//
		// echo inputs
		//
		$this->xm($this->xmfield ("inputs",	
		$this->xmfield("oldPINHash",$oldPinHash).
		$this->xmfield("newPINHash",$newPinHash).	
		$this->xmfield("trackingNumber",$tracking) ));
		
		// check to make sure the entry is correct
		$query = "SELECT * from tracking_number WHERE tracking_number = '$tracking' AND (encrypted_pin='$oldPinHash')";

		$result = $this->dbexec($query,"can not select from tracking_table in updatePIN - ");
		$rows = mysql_num_rows($result);
		if ($rows != 1) $this->xmlend("Can not find requested tracking number with given credentials"); 

    $trackingNumber = mysql_fetch_object($result);
    
    // Update the tracking number table
    $update = "update tracking_number set encrypted_pin = '$newPinHash' where tracking_number ='$tracking'";
		$result = $this->dbexec($update,"cannot update rights table with new guid");
		
		// return outputs
		// docid,rightsid,mcid
		$this->xm($this->xmfield("status","ok"));
	}
}

// main
$x = new updatePINWs();
$x->handlews("updatePIN_Response");

?>
