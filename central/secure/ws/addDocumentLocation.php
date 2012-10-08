<?php


/*
addDocumentLocation

Adds a row in the document_location table; a corresponding row (as specified by the document's guid)
must already exist in the document table.

*/

require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class addDocumentLocationWs extends dbrestws {

	function xmlbody() {

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$node = $this->cleanreq('node');
		$ekey = $this->cleanreq('ekey');
		$intstatus = $this->cleanreq('intstatus');

		// echo inputs

		$this->xm($this->xmfield("inputs", $this->xmfield("ekey", $ekey).$this->xmfield("node", $node).$this->xmfield("intstatus", $intstatus).$this->xmfield("guid", $guid)));

		// find the docid of the document at (guid)

		$docid = $this->finddocument($guid);
		if ($docid == "") {
			$this->xm($this->xmfield("status", "can't find guid $guid"));

		} else {

			// put an entry in the document location table
			$locid = $this->adddocumentlocation($docid, $node, $ekey, $intstatus);

			// return outputs

			$this->xm($this->xmfield("outputs", $this->xmfield("docid", $docid).$this->xmfield("status", $status)));
		}
	}
}

//main

$x = new addDocumentLocationWs();
$x->handlews("addDocumentLocation_Response");
?>