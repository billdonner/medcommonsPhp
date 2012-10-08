<?php


/*
Returns the decryption key for the specified document at the specified node.

It might make sense to also test the status of the file - perhaps if the file
failed an integrity check it should return some type of warning. 

*/

require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
class getDocumentDecryptionKeyWs extends dbrestws {

	function xmlbody() {

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$storageId = $this->cleanreq('storageId');
		$node = $this->cleanhost();
    $nodeId = $node->node_id;

		// echo inputs
		$this->xm($this->xmfield("inputs", $this->xmfield("node", $nodeId).$this->xmfield("guid", $guid)));

		// find the docid of the document at (guid)
		$docid = $this->finddocument($storageId,$guid);
		
		if ($docid == "") {
			$status = "failed";
			$this->xm($this->xmfield("status", "can't find guid $guid"));
    } 
    else {
			$ekey = $this->finddocumentDecryptionKey($guid, $docid, $nodeId);
			
			if($ekey == "") {
				$status = "failed";
				$this->xm($this->xmfield("outputs", $this->xmfield("status", $status)));
      } 
      else {
				// return outputs
				$status = "ok";		
        $this->xm($this->xmfield("outputs", $this->xmfield("entry", $this->xmfield("encrypted_key", $ekey).$this->xmfield("guid", $guid).$this->xmfield("status", $status))));
			}
		}
	}
}

//main

$x = new getDocumentDecryptionKeyWs();
$x->handlews("getDecryptionKey_Response");
?>
