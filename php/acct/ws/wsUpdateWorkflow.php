<?php
require_once "wslibdb.inc.php";
require_once "mc.inc.php";
require_once "utils.inc.php";

/**
 * updateWorkflow
 *
 * Updates or adds a workflow item
 *
 * Inputs:
 *    key - externally supplied key that may be used to uniquely identify this item.
 *    src_accid - account id modifying or updating the item
 *    target_accid - account id that is the subject of the item
 *    type - type of the workflow item
 *    status - status of the workflow item
 *    auth - auth parameter proving access to target account
 */
class updateWorkflowWs extends dbrestws {
	function xmlbody(){

    $srcAccid = req('src_accid');
    $targetAccid = req('target_accid');
    $type = req('type');
    $status = req('status');
    $key = req('key');
    $auth = req('auth');

    error_log("src: $srcAccid trg: $targetAccid");

    // Validate
    if(!is_valid_mcid($srcAccid,true) || !is_valid_mcid($targetAccid,true) || !is_safe_string($type,$status,$key)) {
      $this->xmlend("invalid input");
    }

    $result = $this->dbexec("select wi_id from workflow_item where wi_source_account_id = '$srcAccid' and wi_target_account_id = '$targetAccid' and wi_key = '$key'",
                            "failed to query workflow_item -");

    $wi = mysql_fetch_row($result);
    if($wi) { // Exists already, do update
     // BUG Mark all workflow items for same patient. Should be marking individual item.
       $this->dbexec("update workflow_item set wi_status = '$status' where wi_target_account_id = '$targetAccid'",
                     "unable to update workflow_item $key -");

    }
    else { // Does not exist, insert
       $this->dbexec("insert into workflow_item                                                                                                                                                           
                     (wi_source_account_id, wi_target_account_id, wi_key, wi_type, wi_status)                                                                                                            
                     values ('$srcAccid','$targetAccid','$key','$type','$status')", "unable to insert workflow_item -");
        // BUG: Then mark all workflow items with same state; otherwise they won't show up in the worklist.
       $this->dbexec("update workflow_item set wi_status = '$status' where wi_target_account_id = '$targetAccid'",
                     "unable to update workflow_item $key -");
      
    }
    $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
	}
}

global $is_test;
if(!$is_test) {
  //main
  $x = new updateWorkflowWs();
  $x->handlews("updateWorkflow_Response");
}
?>
