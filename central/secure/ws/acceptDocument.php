<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class acceptDocumentWs extends dbrestws {

	function xmlbody(){
		
		// pick up and clean out inputs from the incoming args
		
		$guid = $this->cleanreq('guid');
		$mcid = $this->cleanreq('mcid');
		$status = $this->cleanreq('status');

		//
		// echo inputs
		//
		$this->xm($this->xmfield ("inputs",	$this->xmfield("guid",$guid)));

		//
		// find document id from document table by guid
		//
		
    // ssadedin - 10/24/05 - added join so that update hits the correct track#
    // otherwise for docs with the same guid the wrong one may get updated
    // nb: this whole block of stuff might be better executed as a single update
    // with a join across all the tables.
		$query = "SELECT d.* from document d, rights r WHERE (guid='$guid') and r.document_ID = d.id and r.user_medcommons_user_id = $mcid";
    error_log($query);
		$result = $this->dbexec($query,"can not select from table document - ");
		if ($result===false) return ($this->xmfield("document", $result));
		
		$l = mysql_fetch_object($result);
		$docid = $l->id; 
		//
		// find rights from document id
		//
		
		$query = "SELECT * from rights WHERE (document_ID='$docid')";
		$result = $this->dbexec($query,"can not select from table rights - ");
		if ($result===false) return ($this->xmfield("rights", $result));
		
		$l = mysql_fetch_object($result);
		$mcid = $l->user_medcommons_user_id;
		$old_accepted_status = $l->accepted_status; 
    if($status) {
      $accepted = $status;
    }
    else {
      $accepted = "accepted";
    }
    error_log("Updating doc $docid");
		
		// set accepted_status to "accepted"
		// wld102005 - add where clause to just update one document
		$update="UPDATE rights SET accepted_status = '$accepted' WHERE (document_ID='$docid')";
		$this->dbexec($update,"can not update table rights - ");
		//
		
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("docid",$docid).		$this->xmfield("mcid",$mcid).
		$this->xmfield("previous_accepted_status",$old_accepted_staus).
		$this->xmfield("status","ok")));
	}
}

//main

$x = new acceptDocumentWs();
$x->handlews("acceptDocument_Response");



?>
