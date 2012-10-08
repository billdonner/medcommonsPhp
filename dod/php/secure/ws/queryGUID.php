<?php
require_once "securewslibdb.inc.php";
require_once "mc.inc.php";
/**
 * queryGUIDWs 
 *
 * Queries the GUID for a specified tracking number
 *
 * Inputs:
 *    trackingNumber - tracking number
 *
 * Returns:
 *    guid corresponding to requested tracking number
 */
class queryGUIDWs extends dbrestws {

	function xmlbody(){
		
    try {
      // pick up and clean out inputs from the incoming args
      $tracking = clean_tracking_number($this->cleanreq('trackingNumber')); 

      $auth = req('auth');
      $auth = str_replace("token:","",$auth);

      //
      // echo inputs
      //
      $this->xm($this->xmfield ("inputs",	
        $this->xmfield("trackingNumber",$tracking) ));

      if(!is_valid_tracking_number($tracking)) {
        $this->xm($this->xmfield("status","failed"));
        return;
      }
      
      $doc = $this->resolveTracking($tracking,null,$auth);
      $guid = "";
      if($doc !== false) {
        $guid = $doc->guid;
      }
      $this->xm($this->xmfield("outputs",$this->xmfield("guid",$guid)),$this->xmfield("status","ok"));
    }
    catch(Exception $ex) {
      error_log("Failed to resolve tracking number $tracking to guid: ".$ex->getMessage());
      $this->xm($this->xmfield("status","failed - unable to resolve tracking number to guid: ".$ex->getMessage()));
    }
	}
}

// main
$x = new queryGUIDWs();
$x->handlews("queryGUID_Response");

?>
