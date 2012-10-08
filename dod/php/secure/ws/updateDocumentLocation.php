<?php

/*
void updateDocumentLocation(java.lang.String guid,
                            java.lang.String nodeName,
                            java.lang.String encryptionKey,
                            int integrityStatus)

    Updates an existing DocumentLocation object for a given guid and nodename with a new encryptionKey and integrityStatus.

    The server will set the integrityCheck timestamp to the current server time.

    Parameters:
        guid - 
        nodeName - 
        encryptionKey - 
        integrityStatus - 
        integrityCheckTime - 

*/

require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
class updateDocumentLocationWs extends dbrestws {

	function xmlbody() {

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$node = $this->cleanreq('node');
		$ekey = $this->cleanreq('ekey');
		$intstatus = $this->cleanreq('intstatus');
		$storageId = $this->cleanreq('storageId');

		// echo inputs
		//

		$this->xm($this->xmfield("inputs", $this->xmfield("ekey", $ekey).$this->xmfield("node", $node).$this->xmfield("intstatus", $intstatus).$this->xmfield("guid", $guid)));

		// find the docid of the document at (guid)

		$docid = $this->finddocument($storageId,$guid);
		if ($docid == "") {
			$this->xm($this->xmfield("status", "can't find (guid,node)"));
			exit;
		}

		// update an entry in the document location table
		$status = $this->updatedocumentlocation($docid, $node, $ekey, $intstatus);

		// return outputs

		$this->xm($this->xmfield("outputs", $this->xmfield("docid", $docid).$this->xmfield("status", $status)));
	}
}

//main

$x = new updateDocumentLocationWs();
$x->handlews("updateDocumentLocation_Response");
?>
