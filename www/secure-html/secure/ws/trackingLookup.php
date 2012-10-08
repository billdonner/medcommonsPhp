<?PHP

require "dbparams.inc.php";
require_once "../ws/wslibdb.inc.php";
//webservice should move to /ws at some point
class trackingLookupWs extends dbrestws {

	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$tracking = $this->cleanreq('tracking');
		$hpin = $this->cleanreq('hpin'); // if present it is the hashed pin value

		$tracking = str_replace(array(' ','=','?',':','-'),"",$tracking);
		xmltop(); //not in debug mode

		// a1 must be present to get anything done at all
		$query = "SELECT * from tracking_number
							 WHERE (tracking_number = '$tracking') and (encrypted_pin = '$hpin')";
		$result = mysql_query ($query) or xmlend("can not query table tracking_number - ".mysql_error());
		
		$count = 0;
		if ($result!="") {
			while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
				xm("<entry>");
				z($l,'tracking'); z($l,'hpin');
				xm("</entry>");
				$count++;
			}

		}
		xmlend(($count==0)?"failure":"success");
	}
	
}
	//main

	$x = new trackingLookupWs();
	$x->handlews("trackingLookup_Response");
?>