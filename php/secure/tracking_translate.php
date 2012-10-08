<?PHP
  /*
   * Searches for the given input as a track#, if found returns the gateway url for it.
   *
   * @param tracking - the track# to check
   * @return JSON result object with properties 'status','url' and 'guid'
   */
  require "dbparams.inc.php";
  require_once "track.inc.php";
  require_once("JSON.php");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  $tracking = htmlentities($_REQUEST['tracking']);
  $tracking = str_replace(array(' ','=','?',':','-'),"",$tracking);

  $result->status = true; // OK
  $result->tn = $tracking;
	dbconnect();
	$gatewayurl = tracking_to_node_guid($tracking,$guid);

  if($gatewayurl == false) {
    $result->status = false;
    $result->url = "";
    $result->guid = "";
  }
  else {
    $result->status = true;
    $result->url = $gatewayurl;
    $result->guid = $guid;
  }
  $json = new Services_JSON();
  echo $json->encode($result);
?>
