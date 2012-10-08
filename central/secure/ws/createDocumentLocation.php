<?php
/*
createDocumentLocation

void createDocumentLocation(java.lang.String guid,
                            java.lang.String nodeName,
                            java.lang.String encryptionKey)

    Creates a DocumentLocation object for a document to be at a specified node with an encryption key. The values of integrityStatus will be set to VALID (zero) and the integrity check time will be set to the current server time.

    Parameters:
        guid - 
        nodeName - 
        encryptionKey - 

*/

require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class createDocumentLocationWs extends dbrestws {


	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$node = $this->cleanreq('node');
		$ekey = $this->cleanreq('ekey');
		$intstatus = $this->cleanreq('intstatus');
		
		

		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",
		$this->xmfield("ekey",$ekey).
		$this->xmfield("intstatus",$intstatus).
		$this->xmfield("node",$node).
		$this->xmfield("guid",$guid)));

		$docid = finddocument($guid);
		if ($docid=="") {$this->xm($this->xmfield("status","can't find $guid ")); exit;}
	

		// put an entry in the document location table
		$locid = $this->adddocumentlocation($docid, $node, $ekey, $intstatus); //wld 11/22/05

		// return outputs

		$this->xm($this->xmfield ("outputs",
		$this->xmfield("docid",$docid).
		$this->xmfield("locid",$locid).
		$this->xmfield("status","ok")));
	}
}

//main

$x = new createDocumentLocationWs();
$x->handlews("createDocumentLocation_Response");
?>