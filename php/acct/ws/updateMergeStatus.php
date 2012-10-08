<?php
require_once "wslibdb.inc.php";
require_once "mc.inc.php";
require_once "utils.inc.php";

/**
 * updateMergeStatus
 *
 * Sets the merge_status of the specified CCR in the ccrlog
 *
 * Inputs:
 *    accid - account id to set the merge_status for
 *    guid  - guid of document to set merge_status for
 *    merge_status  - merge_status to set
 */
class updateMergeStatusWs extends dbrestws {
	function xmlbody(){
    $accid = clean_mcid($_REQUEST['accid']);
    $guid = $_REQUEST['guid'];
    $status = req('status');

    // Validate
    if(!is_valid_guid($guid)  || !is_valid_mcid($accid) || (preg_match("/^[a-z]*$/i",$status)===false)) {
      $this->xm($this->xmfield ("outputs",$this->xmfield("status","failed").$this->xmfield("error","Invalid input")));
    }

    $update = "update ccrlog set merge_status = '$status' where guid = '$guid' and accid = $accid";
		$result = $this->dbexec($update,"can not update - ".mysql_error());
    $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
	}
}

//main

$x = new updateMergeStatusWs();
$x->handlews("updateMergeStatus_Response");

?>
