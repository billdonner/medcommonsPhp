<?php
require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
//modified by wld on 112005 to put guids in document_location table
class validateWs extends dbrestws {

	function xmlbody(){
    try {
      //
      // get clean inputs
      //
      $trackingNumber=$this->cleanreq('trackingNumber');
      $pinHash =$this->cleanreq('pinHash');
      
      // echo inputs
      $this->xm($this->xmfield ("inputs",
      $this->xmfield("trackingNumber",$trackingNumber).
      $this->xmfield("pinHash",$pinHash)));

      // First resolve the document
      $doc = $this->resolveTracking($trackingNumber,$pinHash);

      $status = "not found";
      if($doc != false) {
        $status = "Failed to create public rights to tracking number";

        // Add rights to NULL user for the given document
        $insert = "INSERT INTO rights (rights_id,groups_group_number,account_id,document_id,rights,creation_time)
                   VALUES (NULL,0, '$PUBLIC_MEDCOMMONS_ID', ".$doc->document_id.",'RW',CURRENT_TIMESTAMP)";
        $result = $this->dbexec($insert,"can not insert new rights entry for ".$doc->guid);
        if($result !== false) {
          $status = "success";
        }
      }

      // return outputs
      $this->xm($this->xmfield ("outputs",
        $this->xmfield("status",$status)));
    }
    catch(Exception $ex) {
      error_log("Failed to clear pin for tracking number $tracking: ".$ex->getMessage());
      $this->xm($this->xmfield("status","failed - unable to resolve tracking number to guid: ".$ex->getMessage()));
    }
	}
}

//main

$x = new validateWs();
$x->handlews("validate_Response");



?>
