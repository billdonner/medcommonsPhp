<?php
/**
 * Adds a new entry in the CCR
 */
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class addCCRLogEntryWs extends dbrestws {


	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$date = $this->cleanreq('date');
			$accid = $this->cleanreq('accid');
		
				$guid = $this->cleanreq('guid');

						$from = $this->cleanreq('from');

								$to = $this->cleanreq('to');

										$subject = $this->cleanreq('subject');

												$status = $this->cleanreq('status');

													


		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",
		$this->xmfield("id",$guid)
		));

		//
		// add to the CCRLogEntry table
		
		
	$insert="INSERT INTO ccrlog(accid, guid,status, date ,src, dest,subject) ".
				"VALUES('$accid','$guid','$status', NOW(),'$from','$to','$subject')";
			$timenow=time();
				$this->dbexec($insert,"can not insert into table ccrlog - ");
			$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
		} 


}

//main

$x = new addCCRLogEntryWs();
$x->handlews("addCCRLogEntry_Response");
?>