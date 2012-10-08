<?php


/*
addDocumentLocation

Adds a row in the document_location table; a corresponding row (as specified by the document's guid)
must already exist in the document table.

*/

require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
class addDocumentLocationWs extends dbrestws {

	function xmlbody() {

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$ekey = $this->cleanreq('ekey');
		$storageId = $this->cleanreq('storageId');
		$intstatus = $this->cleanreq('intstatus');
    $node = $this->cleanhost();
    $nodeId = $node->node_id;

		// echo inputs
		$this->xm($this->xmfield("inputs", $this->xmfield("ekey", $ekey).$this->xmfield("node", $nodeId).$this->xmfield("intstatus", $intstatus).$this->xmfield("guid", $guid)));

		// find the docid of the document at (guid)
		$docid = $this->finddocument($storageId,$guid);
		if ($docid == "") {
			$this->xm($this->xmfield("status", "can't find guid $guid"));
    } 
    else {

			// put an entry in the document location table
			$locid = $this->adddocumentlocation($docid, $nodeId, $ekey, $intstatus);

			// return outputs
			// wld 06sep06 $status was undefined, swapped in $intstatus
			$this->xm($this->xmfield("outputs", $this->xmfield("docid", $docid).$this->xmfield("status", $intstatus)));
		}
	}
}

// main
$x = new addDocumentLocationWs();
$x->handlews("addDocumentLocation_Response");
?>
