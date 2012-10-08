<?php


/**
 * Deletes an existing DocumentLocation object for a given guid and nodename.
 * 
 * <P>
 * This action should be performed by the storage layer when the item is deleted from disk.
 * <P>
 * 
 * 
 * @param guid
 * @param nodeName

 */



require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
class deleteDocumentLocationWs extends dbrestws {
	function xmlbody() {

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$storageId = $this->cleanreq('storageId');
    $node = $this->cleanhost();
    $nodeId = $node->node_id;

		// echo inputs
		$this->xm($this->xmfield("inputs", $this->xmfield("node", $nodeId).
					$this->xmfield("guid", $guid)));

		// find the docid of the document at (guid)
		$docid = $this->finddocument($storageId,$guid);
		
		if ($docid == "") {
			$this->xmlend("can't find (guid,node)");
			exit;
		}

		// update an entry in the document location table
		$status = $this->deletedocumentlocation($docid, $nodeId);

		// return outputs
		$this->xm($this->xmfield("outputs", $this->xmfield("docid", $docid).
		$this->xmfield("status", $status)));
	}
}

//main

$x = new deleteDocumentLocationWs();
$x->handlews("deleteDocumentLocation_Response");
?>
