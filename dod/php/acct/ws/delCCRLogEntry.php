<?php
// set or unset the RedCcr

require_once "wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class delCCRWs extends dbrestws {

	function xmlbody(){

		// pick up and clean out inputs from the incoming args

		$accid = $this->cleanreq('accid');

		$guid = $this->cleanreq('guid');


			$ob = "UPDATE ccrlog SET status='DELETED' WHERE (accid = '$accid') AND  (guid='$guid')";
			$this->dbexec($ob,"can not update1 table ccrlog to delete - ".mysql_error());

			$rowcount = mysql_affected_rows();
			if ($rowcount == 0) $this->xmlend("could not delete ccr via  $ob");
		

				// update the time we reset this

		$timenow = time();		
		$ob= "UPDATE users SET  ccrlogupdatetime = '$timenow' where (mcid = '$accid')";

		$this->dbexec($ob,"can not update users in delCCRLogEntry");
		// update the time we reset this
	
		$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok $ob")));
	}


}

//main

$x = new delCCRWs();
$x->handlews("delCCRLogEntry_Response");





?>