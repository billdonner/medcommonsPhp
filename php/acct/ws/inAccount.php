<?php
require_once "wslibdb.inc.php";
/**
 * inAccountWs 
 *
 * Returns true or false if a given tracking number is in the specified account
 *
 * Inputs:
 *    trackingNumber - tracking number to check
 *    accid - account id to check
 */
class inAccountWs extends dbrestws {
	function xmlbody(){
		// pick up and clean out inputs from the incoming args
		$accid = $this->cleanreq('accid');
		$tn = $this->cleanreq('trackingNumber');
		$sql = "select 1 from ccrlog where tracking = '$tn' and accid = '$accid'";
		$result = $this->dbexec($sql,"can not select from ccrlog - ".mysql_error());
    $rowcount = mysql_num_rows($result);
    if($rowcount == 0) {
      $output = "false";
    }
    else {
      $output = "true";
    }
    $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok"). $this->xmfield("result",$output)));
	}
}

//main

$x = new inAccountWs();
$x->handlews("inAccount_Response");





?>
