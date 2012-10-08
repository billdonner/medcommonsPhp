<?php
require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
class allocateTrackingNumberWs extends dbrestws {


	function xmlbody(){
		// pick up and clean incoming arguments

		//
		// echo inputs
		//
	

		//
		// make up a tracking number, and a corresponding medcommons id if needed
		$tracking = $this->generate_tracking();
		
		// wld 102105 if we didn't get a good tracking nmber then return immediately
		if ($tracking=='') $this->xmlend("internal error - could not allocate tracking number");
		
		//
		// return outputs
		//
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("trackingNumber",$tracking).
		$this->xmfield("status","ok")));
	}
}

//main

$x = new allocateTrackingNumberWs();
$x->handlews("allocateTrackingNumber_Response");
?>