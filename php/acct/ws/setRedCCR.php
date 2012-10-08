<?php
// set or unset the RedCcr

require_once "wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class setRedCCRWs extends dbrestws {


	function xmlbody(){

		// pick up and clean out inputs from the incoming args

		$accid = $this->cleanreq('accid');

		$guid = $this->cleanreq('guid');

		$clear = $this->cleanreq('clear');

		//$einfo = mysql_escape_string($_REQUEST('einfo'));
		$einfo = $_REQUEST['einfo'];

		if ($clear!=1) // case where we are setting, clear existing
		{
			$ob = "UPDATE ccrlog SET status='WASRED', einfo='' WHERE (accid = '$accid') AND (status ='RED')";
			$this->dbexec($ob,"can not update1 table ccrlog - ".mysql_error());

			$ob = "UPDATE ccrlog SET status='RED',einfo='$einfo' WHERE (guid ='$guid') and (accid = '$accid')";
			$this->dbexec($ob,"can not update2 table ccrlog - ".mysql_error());
			$rowcount = mysql_affected_rows();
			if ($rowcount == 0) $this->xmlend("could not set red ccr via  $ob");
		}
		else // case where we are clearing, just do it
		{
			$ob = "UPDATE ccrlog SET status='WASRED', einfo='' WHERE (accid = '$accid') AND (status ='RED') AND (guid='$guid')";
			$this->dbexec($ob,"can not update1 table ccrlog to clear - ".mysql_error());

			$rowcount = mysql_affected_rows();
			if ($rowcount == 0) $this->xmlend("could not clear red ccr via  $ob");
		}

				// update the time we reset this

		$timenow = time();		
		$ob= "UPDATE users SET  ccrlogupdatetime = '$timenow' where (mcid = '$accid')";

		$this->dbexec($ob,"can not update users in addCCRLogEntry");

    // Insert into document_type table
    $insert = "insert into document_type (dt_id, dt_account_id, dt_type, dt_guid, dt_privacy_level,dt_comment) values
            (NULL, '$accid','EMERGENCYCCR','$guid', 'Private','Emergency CCR');";

		$result = $this->dbexec($insert,"can not insert - ".mysql_error());

		// update the time we reset this
		$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok $ob")));
	}


}

//main

$x = new setRedCCRWs();
$x->handlews("setRedCCR_Response");





?>
