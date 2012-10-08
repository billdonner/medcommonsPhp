<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
//modified by wld on 112005 to put guids in document_location table
class validateWs extends dbrestws {

	function xmlbody(){
		//
		// get clean inputs
		//
		$trackingNumber=$this->cleanreq('trackingNumber');
		$pinHash =$this->cleanreq('pinHash');
		
		
		//
		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",
		$this->xmfield("trackingNumber",$trackingNumber).
		$this->xmfield("pinHash",$pinHash)));


		$select ="SELECT * FROM tracking_number
					WHERE ((tracking_number='$trackingNumber') and
					(encrypted_pin = '$pinHash'))";

		$result = $this->dbexec($select,"can not select from table tracking_number - ");
		$count = mysql_numrows($result);
		$status = "ok";
		if ($count<1) $status = "not found"; else if ($count >1) $status = "too many matches";
		if ($status!="ok") {$this->xmfield ("lookup",$status); $this->xmlend ($status);}
		$trobj = mysql_fetch_object($result);
		$rights_id = $trobj->rights_id;
	
		// go to the rights table to get the document id
		$select="SELECT * FROM rights WHERE (rights_id = '$rights_id')";
		$result = $this->dbexec($select,"can not select from table rights - ");
		$robj = mysql_fetch_object($result);
		if ($robj===FALSE) $this->xmlend("internal failure to find record in rights table");

		
		$docid =$robj->document_ID;
	
		// go to the document table to get the guid
		$select="SELECT * FROM document WHERE (id = '$docid')";
		$result = $this->dbexec($select,"can not select from table document - ");
		$dobj = mysql_fetch_object($result);
		if ($dobj===FALSE) $this->xmlend("internal failure to find record in document table");

		// go to the document location table to get a nodeid
		$guid = $dobj->guid;
		
		$select="SELECT * FROM document_location WHERE (document_id = '$docid')";
		$result = $this->dbexec($select,"can not select from table document_location - ");
		
		$dlobj = mysql_fetch_object($result);
		if ($dlobj===FALSE) $this->xmlend("internal failure to find record in document_location table");
		
		$nodeid = $dlobj->node_node_id; // really need a while loop or a join
		
		// go to the node table to get hostname, keys, etc
		$select="SELECT * FROM node WHERE (node_id = '$nodeid')";
		$result = $this->dbexec($select,"can not select from table node - $nodeid " );
		
		$nobj = mysql_fetch_object($result);
		if ($nobj===FALSE) $this->xmlend("internal failure to find record in node table $nodeid");
		//
		// return outputs
		//
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("mcid",$robj->user_medcommons_user_id).
		$this->xmfield("docid",$docid).
		$this->xmfield("guid",$dobj->guid).
		$this->xmfield("attributions",$dobj->attributions).

		$this->xmfield("accepted_status",$robj->accepted_status).

		$this->xmfield("host",$nobj->hostname).
		$this->xmfield("node",$nobj->node_id).
		$this->xmfield("ekey",$dlobj->encryption_key).
		$this->xmfield("status",$status)));
	}
}

//main

$x = new validateWs();
$x->handlews("validate_Response");



?>
