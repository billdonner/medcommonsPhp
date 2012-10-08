<?php
require_once "../ws/securewslibdb.inc.php";
require_once "../db.inc.php";

/**
 * Register a document in medcommons and also create 
 * a tracking number for it.
 *
 * @author ssadedin@medcommons.net
 */
class registerTrackDocumentWs extends dbrestws {
	
	function xmlbody(){

    try {
      // pick up and clean out inputs from the incoming args
      $mcid =$this->cleanreq('mcid');
      $guid = $this->cleanreq('guid');
      $db = DB::get();

      $rights = req('right');
      if(!$rights)
        $rights = array();

      $pinHash = $this->cleanreq('pinHash');
      $pin = $this->cleanreq('pin');
      $auth = req('auth');

      $constraint = req('constraint', 'UNLIMITED');

      if(!$auth || $auth ==="")
        throw new Exception("Missing parameter auth");

      $intstatus = $this->cleanreq('intstatus');

      // Optional expiry
      $expirySeconds=$this->cleanreq('expirySeconds');
      
      $tracking = $this->generate_tracking(); // pick one out of thin air
      
      $storageId = $mcid;
      
      if($storageId == $GLOBALS['PUBLIC_MEDCOMMONS_ID']) {

        // For POPS the rights inherited will always be RW
        $callerRights = "RW";

        // if medcommonsid is zeroes for POPS, then generate an mcid and tracking number such that 
        // mcid = tracking number with special code prepended
        $mcid = "0000".$tracking; // 16 digit mcid
      }
      else
        $callerRights = get_rights($auth, $storageId);

      // echo inputs
      $this->xm($this->xmfield ("inputs",	
      $this->xmfield("mcid",$mcid).
      $this->xmfield("right",$rights).
      $this->xmfield("pinHash",$pinHash).		
      $this->xmfield("intstatus",$intstatus).
      $this->xmfield("guid",$guid)));
      
      // wld 102105 if we didn't get a good tracking nmber then return immediately
      if($tracking=='') 
        throw new Exception("could not allocate tracking number");

      // Only create the document if it is not already there    
      $docids = $db->query("select id from document where guid = ?",array($guid));

      if(count($docids) == 0) {
        // add to the document table
        $doc_id = $db->execute("INSERT INTO document (guid,storage_account_id) VALUES(?,?)", array($guid, $storageId));
      }
      else {
        $docidRow = $docids[0];
        $doc_id = $docidRow->id;
      }

      // add an entry to the rights table for the creator of the document
      // note because they are creating it we give them full RW access even
      // if they do not have that level of access to the patient account
      // May need to review this one day.
      $rights_id = $db->execute("INSERT INTO rights (account_id,document_id,rights) VALUES(?,?,?)",
                array($storageId, $doc_id, 'RW')); 

      $es_id = null;

      // If a pin was provided, enable PIN access to this document
      // by adding an external share linked to the tracking number
      if(($pin !== null) && ($pin !== "")) {
        // add an external share
        $es_id = $db->execute("insert into external_share (es_id, es_identity, es_identity_type) values (NULL,?,'PIN')",
            array($tracking."/".$pin));

        // If storing for a real user then we grant the tracking number access
        // to their whole account.  If however it is for a pops user then
        // the access is only granted for the single document.
        if($storageId === $GLOBALS['PUBLIC_MEDCOMMONS_ID'])
          $rights_storage_id = null;
        else
          $rights_storage_id = $storageId;

        // add a rights entry for the tracking number itself
        $rights_id = $db->execute("INSERT INTO rights (document_id,es_id, storage_account_id, rights) VALUES(?,?,?,?)",
                  array($doc_id, $es_id, $rights_storage_id, $callerRights)); // note: 2nd last param gives tracking number access to whole acct
      }

      // update the tracking number to amend it with the pin and external share
      $update = "update tracking_number set encrypted_pin=?,pin=?,es_id=?,doc_id=?,access_constraint=?";
      if($expirySeconds)
        $update .= ", expiration_time = date_add(CURRENT_TIMESTAMP, interval $expirySeconds second)";
      $update .= " WHERE (tracking_number = '$tracking' AND encrypted_pin = '999999999999')";			
      $db->execute($update,array($pinHash, $pin, $es_id, $doc_id, $constraint));
      
      // If additional rights entries provided, add them in
      if(is_array($rights)) {
        dbg("found ".count($rights)." additional rights entries");
        /*$s = $db->connect()->prepare("INSERT INTO rights(rights_id, document_id, account_id, rights) VALUES (NULL, ?,?,?)");
        if(!$s)
          throw new Exception("Failed to prepare rights insert sql");
         */

        foreach($rights as $rs) {
          dbg("adding right ".$rs);
          $r = split("=",$rs);
          $db->execute("INSERT INTO rights(rights_id, document_id, account_id, rights) VALUES (NULL, ?,?,?)",
                        array($doc_id, $r[0], $r[1]));
          /*if(!$s->execute(array($doc_id, $r[0], $r[1])))
            throw new Exception("Failed to insert rights doc_id=".$doc_id." accid=".$r[0]." rights=".$r[1]);
           */
          // $s->closeCursor();
        }
      }
      
      // return outputs
      $this->xm($this->xmfield ("outputs",
      $this->xmfield("trackingNumber",$tracking).
      $this->xmfield("docid",$doc_id).
      $this->xmfield("rightsid",$rights_id).
      $this->xmfield("mcid",$storageId).
      $this->xmfield("status","ok")));

    }
    catch(Exception $e) {
      $this->xmlend("internal error  - ".$e->getMessage());
    }
	}
}

//main

$x = new registerTrackDocumentWs();
$x->handlews("registerTrackDocument_Response");



?>
