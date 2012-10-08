<?php
require_once "../ws/securewslibdb.inc.php";
require_once "mc.inc.php";
require_once "utils.inc.php";

/**
 * Registers a new document in the document table.
 * <p>
 * If the specified guid,storageId pair already exist then the call 
 * succeeds, but no new entry is created.
 * <p>
 * 
 * @param guid - guid of document to register
 * @param storageId - storageId of document to register
 * @param paymentType - optional, type of payment required for document
 * @param paymentQuantity - optional, quantity to be paid for
 */
class addDocumentWs extends dbrestws {


	function xmlbody(){
    try {

      // pick up and clean out inputs from the incoming args
      $guid = req('guid');
      $storageId = req('storageId');
      $chargeType = req('chargeType');
      $chargeQuantity = req('chargeQuantity');

      if(!is_valid_mcid($storageId,true)) 
        throw new Exception("Invalid input storageId (value=$storageId)");

      if(!is_valid_guid($guid))
        throw new Exception("Invalid input guid (value=$guid)");

      if(($chargeType != null) &&  !is_safe_string($chargeType))
        throw new Exception("Invalid input chargeType (value=$chargeType)");
      
      if(($chargeQuantity != null) && (preg_match("/^[0-9]{1,12}$/",$chargeQuantity) !== 1))
        throw new Exception("Invalid input chargeQuantity (value=$chargeQuantity)");

      // process optional host arg if any
      $this->gethostarg();
      
      // echo inputs
      $this->xm($this->xmfield ("inputs",
      $this->xmfield("guid",$guid)));

      // add to the document table
      $docid = $this->finddocument($storageId,$guid);
      if ($docid == "") {
        // Insert
        $db = DB::get();
        $timenow=time();
        $docId = $db->execute("INSERT INTO document (guid,storage_account_id,creation_time) VALUES (?,?,NOW())", array($guid,$storageId));

        // if charge specified, add the charge
        if(($chargeType != null) && ($chargeType != "")) {
          $db->execute("insert into document_charge (dc_id, dc_document_id, dc_charge_type, dc_quantity, dc_status)
                        values (NULL, ?, ?, ?, ?)", array($docId, $chargeType, $chargeQuantity, 'OUTSTANDING'));
        }

        $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
      } 
      else {  
        // Duplicate document - don't insert but it's not an error.
        $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
      }
    }
    catch(Exception $e) {
      error_log("addDocumentWs failed: ".$e->getMessage());
      $this->xm($this->xmfield ("outputs",$this->xmfield("status","failed")));
      $this->xmlend("failure - unable to add document $guid / $storageId");
    }
	}
}

//main

$x = new addDocumentWs();
$x->handlews("addDocument_Response");
?>
