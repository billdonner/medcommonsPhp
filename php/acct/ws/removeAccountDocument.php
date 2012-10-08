<?php
require_once "wslibdb.inc.php";
/**
 * removeAccountDocumentWs 
 *
 * Removes a document of a particular type from an account.
 *
 * Inputs:
 *    accid - account id to remove document for
 *    documentType - type of document to add.  There are a number of predefined types
 */
class removeAccountDocumentWs extends dbrestws {
	function xmlbody(){
    $docType = $_REQUEST['documentType'];
		$accid = $this->cleanreq('accid');

    if($docType == "") {
      $this->xm($this->xmfield ("outputs",$this->xmfield("status","failed - documentType not provided")));
    }

    // indicate that any old versions of the document have been
    // replaced in the CCR log.
    $result = $this->dbexec("update  ccrlog c, document_type d
                              set c.merge_status = 'Replaced'
                              where c.accid = '$accid'
                              and d.dt_account_id = c.accid
                              and d.dt_guid = c.guid
                              and d.dt_type = '$docType'
                              and (c.merge_status is NULL or c.merge_status <> 'Replaced')","can not select - ".mysql_error());

    $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
	}
}

//main

$x = new removeAccountDocumentWs();
$x->handlews("removeAccountDocument_Response");

?>
