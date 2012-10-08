<?php
require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
class revokeTrackingNumberWs extends dbrestws {


	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$tn = $this->cleanreq('trackingNumber');
		$hp = $this->cleanreq('hashedPin');

		//process optional host arg if any
		//$this->gethostarg();

		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",
		$this->xmfield("trackingNumber",$tn).
		$this->xmfield("hashedPin",$hp)
		
		)
		
		);
		
		// make sure both arguments are supplied
		if ($hp=="") $this->xmlend("needs hashedPin");
		if ($tn=="") $this->xmlend("needs trackingNumber");

		//
		// delete the record from the tracking_number table by setting the pin to an imposible value

		$update="UPDATE tracking_number SET encrypted_pin = '0000000000000000000000000000000000000000'
		               WHERE (tracking_number ='$tn') and (encrypted_pin = '$hp')";
		 
		$this->dbexec($update,"can not update tracking_number table for delete - ");
		//
		$count = mysql_affected_rows();
		if ($count == 0) $this->xmlend("no such tracking number"); else 
		if ($count != 1) $this->xmlend("internal inconsistency");
		$status = "ok";
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("status",$status)));
	}
}

//main

$x = new revokeTrackingNumberWs();
$x->handlews("revokeTrackingNumber_Response");
?>