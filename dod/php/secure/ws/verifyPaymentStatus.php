<?PHP
require_once "wslibdb.inc.php";
require_once "../securelib.inc.php";
require_once "utils.inc.php";
require_once "mc.inc.php";

/**
 * Registers a payment transaction to clear charges registered
 * for a document.
 *
 * @param storageId       account that owns the documents
 * @param guids           documents for which payment should be verified
 * @return                array of objects containing guid and status for each guid
 */
class verifyPaymentStatusWs extends jsonrestws {

	function jsonbody() {

    $storageId = req('storageId');
    if(!is_valid_mcid($storageId,true))
      throw new Exception("Bad value for storageId [ $storageId ]");

    $guids = explode(",",req('guids'));
    $in = '';
    foreach($guids as $guid) {
      if(!is_valid_guid($guid))
        throw new Exception("Bad value for guid [ $guid ]");
      if($in!='')
        $in .= ",";
      $in .= "'$guid'";
    }

    dbg("querying payment status for ".count($guids)." guids for storage id $storageId");

    $db = DB::get();
    $charges = $db->query("select dc.*, d.guid from document_charge dc, document d
                           where dc.dc_document_id = d.id
                             and d.guid in ($in)
                             and d.storage_account_id = ?",array($storageId));
    
    $results = array();
    foreach($charges as $c) {
      $status = new stdClass();
      $status->guid = $c->guid;
      $status->status = $c->dc_status;
      $results[]=$status;
    }
    return $results;
	}
}

// main
global $is_test;
if(!isset($is_test)) {
  $x = new verifyPaymentStatusWs();
  $x->handlews("response_verifyPaymentStatus");
}
?>
