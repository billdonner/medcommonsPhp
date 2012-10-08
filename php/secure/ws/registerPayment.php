<?PHP
require_once "wslibdb.inc.php";
require_once "../securelib.inc.php";
require_once "utils.inc.php";
require_once "mc.inc.php";

/**
 * Registers a payment transaction to clear charges registered
 * for a document.
 *
 * @param storageId       account that owns the document
 * @param guid            document for which payment was made
 * @param <paymentType>   value of a particular counter 
 *                        modified or adjusted by the payment
 *                        types include INBOUND_FAX, DICOM, NEW_ACCOUNT
 */
class registerPaymentWs extends jsonrestws {

  function get_counters() {
      $counter_names = array('NEW_ACCOUNT','INBOUND_FAX','DICOM');
      $counters = array();
      foreach($counter_names as $cn) {
        $value = req($cn);
        if($value === null)
          continue; 

        if(preg_match("/^[0-9]{1,16}$/",$value)!==1)
          throw new Exception("Bad value for counter $cn: $value");
           
        $counters[$cn] = $value;
      } 
      return $counters;
   }

	function jsonbody() {

    $storageId = req('storageId');
    if(!is_valid_mcid($storageId,true))
      throw new Exception("Bad value for storageId [ $storageId ]");

    $guid = req('guid');
    if(!is_valid_guid($guid))
      throw new Exception("Bad value for guid [ $guid ]");

    $counters = $this->get_counters();

    dbg("got ".count($counters)." to update for document $guid, storage id $storageId");

    $db = DB::get();
    foreach($counters as $c => $v) {
      // Find the charge 
      $dc = $db->query("select dc.* from document_charge dc, document d
                        where dc.dc_charge_type = ? 
                          and dc.dc_document_id = d.id
                          and d.guid = ?
                          and d.storage_account_id = ?", array($c, $guid, $storageId));
      dbg("Found ".count($dc)." document charges to update");
      if(count($dc)>0) {
        $dc = $dc[0];
        if($v >= $dc->dc_quantity) {
          $db->execute("update document_charge set dc_status = 'PAID' where dc_id = ?",array($dc->dc_id));
        }
        else 
          throw new Exception("Insufficient credit to resolve payment for $c - provided = $v, required = {$dc->dc_quantity}");
      }
    }
	}
}

// main
global $is_test;
if(!isset($is_test)) {
  $x = new registerPaymentWs();
  $x->handlews("response_registerPayment");
}
?>
