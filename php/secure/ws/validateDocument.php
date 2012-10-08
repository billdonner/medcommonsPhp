<?php

require_once "../ws/securewslibdb.inc.php";
require_once "../db.inc.php";

/**
 * Validate a tracking number and return information about stored 
 * referenced document, as well as authentication token for access
 *
 * @param trackingNumber - tracking number to validate
 * @param pinHash - sha1 hash of pin for tracking number
 *
 * @author ssadedin@medcommons.net
 */
class validateWs extends dbrestws {

	function xmlbody(){

    try {
      // get clean inputs
      $trackingNumber = req('trackingNumber');
      $pinHash = req('pinHash');
      
      // echo inputs
      $this->xm($this->xmfield ("inputs",
      $this->xmfield("trackingNumber",$trackingNumber).
      $this->xmfield("pinHash",$pinHash)));

      $db = DB::get();

      // $this->gethostarg();

      // dbg("node id is ".$this->nodeid);

      $tns = $db->query( "SELECT * FROM tracking_number t, document d, document_location l, node n
                          WHERE t.tracking_number = ? and t.encrypted_pin = ?
                          AND d.id = t.doc_id
                          AND l.document_id = d.id
                          AND n.node_id = l.node_node_id
                          AND t.access_constraint <> 'EXPIRED'",
                          array($trackingNumber, $pinHash));

      if(count($tns)==0)
        throw new Exception("Tracking Number $trackingNumber not found");

      if(count($tns) > 1) 
        throw new Exception("Internal error - multiple entries for $trackingNumber found");
      
      $tn = $tns[0];

      // Create a new authentication token
      $token = generate_authentication_token();
      $db->execute("insert into authentication_token (at_id, at_token, at_es_id) 
                    values (NULL,?,?)", array($token, $tn->es_id));

      dbg("Created authentication token $token for access to tracking number $trackingNumber");

      // If constraint is ONE_TIME then expire the tracking number
      if($tn->access_constraint == "ONE_TIME") {
        dbg("Accessing one time tracking number");
        $db->execute("update tracking_number set access_constraint = 'EXPIRED' where tracking_number = ?", array($trackingNumber));
        $tn->access_constraint = "EXPIRED";
      }

      // return outputs
      $this->xm($this->xmfield ("outputs",
        $this->xmfield("mcid",$tn->storage_account_id).
        $this->xmfield("docid",$tn->doc_id).
        $this->xmfield("guid",$tn->guid).
        $this->xmfield("storageId",$tn->storage_account_id).
        $this->xmfield("host",$tn->hostname).
        $this->xmfield("node",$tn->node_node_id).
        $this->xmfield("node_key",$tn->client_key).
        $this->xmfield("ekey",$tn->encrypted_key).
        $this->xmfield("access_constraint",$tn->access_constraint).
        $this->xmfield("auth",$token).
        $this->xmfield("status","ok")));
    }
    catch(Exception $e) {
      $this->xmlend("internal error  - ".$e->getMessage());
    }
	}
}

//main

$x = new validateWs();
$x->handlews("validate_Response");



?>
