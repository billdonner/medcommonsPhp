<?php
/**
 * Adds a new entry in the document table.
 */
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class addDocumentWs extends dbrestws {


	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		//process optional host arg if any
		$this->gethostarg();

		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",
		$this->xmfield("guid",$guid)));

		//
		// add to the document table
		$docid = $this->finddocument($guid);
		if ($docid == "") {
			// Insert
			$timenow=time();
			$insert="INSERT INTO document (guid,creation_time) "."VALUES('$guid',NOW())";
			$this->dbexec($insert,"can not insert into table document - ");
			$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
		} 
		else{
			// Duplicate document - don't insert but it's not an error.
			$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
		}
		

		
		// return outputs
		//docid,rightsid,mcid
		
	}
}

//main

$x = new addDocumentWs();
$x->handlews("addDocument_Response");
?>