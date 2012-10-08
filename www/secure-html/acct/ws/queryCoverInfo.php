<?php
require_once "wslibdb.inc.php";
/**
 * queryCoverInfoWs 
 *
 * Returns information about a Cover previously registered
 *
 * Inputs:
 *    coverId - the id to query
 */
class queryCoverInfoWs extends dbrestws {
	function xmlbody(){
		// pick up and clean out inputs from the incoming args
		$coverId = $this->cleanreq('coverId');
		$sql = "select * from cover where cover_id = '$coverId'";
		$result = $this->dbexec($sql,"can not select from cover table - ".mysql_error());
    $rowcount = mysql_num_rows($result);
    if($rowcount == 0) {
      $this->xm($this->xmfield ("outputs",$this->xmfield("status","not found")));
    }
    else {
      $cover = mysql_fetch_object($result);
      $this->xm($this->xmfield ("outputs",
        $this->xmfield("status","ok"). 
        $this->xmfield("accountId",$cover->cover_account_id).
        $this->xmfield("encryptedPin",$cover->cover_encrypted_pin).
        $this->xmfield("notification",$cover->cover_notification).
        $this->xmfield("providerCode",$cover->cover_provider_code)
      ));
    }
	}
}

// main
$x = new queryCoverInfoWs();
$x->handlews("queryCoverInfo_Response");





?>
