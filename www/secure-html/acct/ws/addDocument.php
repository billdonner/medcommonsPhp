<?php
require_once "wslibdb.inc.php";
/**
 * addDocumentWs 
 *
 * Adds a document to an account's nominated document table
 *
 * Inputs:
 *    accid - account id to add document for
 *    documentType - type of document to add.  There are a number of predefined types
 *    guid - guid of document to be added
 *    unique - set to true if the document should replace any existing document of the same type
 */
class addDocumentWs extends dbrestws {
	function xmlbody(){
    $docType = $_REQUEST['documentType'];
    $docComment = $_REQUEST['comment'];
    $guid = $_REQUEST['guid'];
		$accid = $this->cleanreq('accid');
		$unique = $this->cleanreq('unique');

    if($docType == "") {
      $this->xm($this->xmfield ("outputs",$this->xmfield("status","failed - documentType not provided")));
    }

    if($unique && ($unique=="true")) {
      $del = "delete from document_type where dt_type='$docType' and dt_account_id = '$accid'";
      $result = $this->dbexec($del,"can not delete - ".mysql_error());
    }

    $insert = "insert into document_type (dt_id, dt_account_id, dt_type, dt_guid, dt_privacy_level,dt_comment) values
            (NULL, '$accid','$docType','$guid', 'Private','$docComment');";
		$result = $this->dbexec($insert,"can not insert - ".mysql_error());
    $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
	}
}

//main

$x = new addDocumentWs();
$x->handlews("addDocument_Response");

?>
