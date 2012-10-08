<?php
/**
 * resolveDocument Service 
 *
 * Attempts to resolve a storage id by which the given guid can be accessed
 * by the given account, according to the rights system.
 *
 * If access is authorized a result flag of "authorized" will be added to the
 * results.   If access can not be authorized, one of two flags will be present:
 * <ul>
 *  <li>unauthorized - a rights entry exists indicating the content is not authorized for this account
 *  <li>unknown      - no information about accessibilty of the content exists
 * </ul>
 */
require_once "../ws/securewslibdb.inc.php";
class resolveDocumentWs extends dbrestws {

	function xmlbody(){

    try {
        // pick up and clean incoming arguments
        $guid=req("guid");
        if(preg_match("/[0-9a-z]{40}/",$guid) !== 1)
          throw new Exception("Bad format for guid: $guid");

        $accid=$_REQUEST['accountId'];
        $auth=$_REQUEST['auth'];

        $allRights = $this->resolveGuid($accid,$guid,$auth);
        dbg("got ".count($allRights)." rights from guid resolution.");

        // echo inputs
        $this->xm($this->xmfield ("inputs",
          $this->xmfield("guid",$guid).
          $this->xmfield("accid",$accid).
          $this->xmfield("auth",$auth)));

        $matchingRights = array();
        if($allRights) {
          $rights_acct = null;
          foreach($allRights as $r) {

            // We only use rights generated from the *first* account
            // To ensure this, we hash the account id and the es_id since
            // both of these may point to the account that owns
            // the rights entry
            if($rights_acct === null)
              $rights_acct = $r->account_id . ":".$r->es_id;

            if($rights_acct != ($r->account_id . ":".$r->es_id))
              break; // not the same account as the first one we saw - quit

            // Note the Array is a legacy hack to workaround old bug
            dbg("got right ".$r->rights." with account ".$r->account_id."/es(".$r->es_id.")");
            if( ($r->rights == "Array") || ($r->rights=="ALL") || (strpos($r->rights,"R")!==false)) {
              $matchingRights[]=$r;
            }
          }
        }

        $result = "unknown";
        if($matchingRights) { // Found matching rights: authorized
          $result = "authorized";
        }
        else
        if(($allRights !== false) && count($allRights)) { // Found rights indicating no access
          $result = "unauthorized";
        }

        // See if we can resolve a tracking number for this document
        $tnOutput = "";
        if(count($matchingRights) > 0) {
          $trackingReference = $this->resolveDocumentTrackingReference($matchingRights[0]->document_id);
          if($trackingReference !== false) {
            $tnOutput = $this->xmfield("trackingReference",
              $this->xmfield("trackingNumber",$trackingReference->tracking_number).
              $this->xmfield("pin",$trackingReference->pin));
          }
        }

        // add results
        $docRefs = "";
        foreach($matchingRights as $r) {
          $docRefs.="<docRef><guid>$guid</guid>"
            .$this->xmfield("rightsId",$r->rights_id)
            .$this->xmfield("rights",$r->rights)
            .$this->xmfield("creationDate",$r->creation_time)
            .$this->xmfield("location_key",isset($r->client_key)?$r->client_key : "")
            .$this->xmfield("location",$r->node_node_id)
            .$this->xmfield("storageId",$r->storage_account_id);

          if($r->dc_id) {
            dbg("found outstanding charge ".$r->dc_id);
            $docRefs.=$this->xmfield("outstandingCharge",$this->xmfield("chargeType",$r->dc_charge_type).$this->xmfield("chargeQuantity",$r->dc_quantity));
          }

          $docRefs .= "</docRef>\n";
        }

        // return outputs
        $this->xm($this->xmfield ("outputs", 
          $this->xmfield("status","ok").
          $this->xmfield("result","$result")
          .$docRefs
          .$tnOutput));
    }
    catch(Exception $e) {
        $this->xm($this->xmfield ("outputs", $this->xmfield("status","failed - ".$e->getMessage())));
    }
  }
}

//main
$x = new resolveDocumentWs();
$x->handlews("resolveDocument_Response");
?>
