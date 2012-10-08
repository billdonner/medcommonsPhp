<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class registerDocumentWs extends dbrestws {

	function xmlbody(){
		
		// pick up and clean out inputs from the incoming args
		$mcid =$this->cleanreq('mcid');
		$guid = $this->cleanreq('guid');
		$ekey = $this->cleanreq('ekey');
		$intstatus = $this->cleanreq('intstatus');
		$rights = $this->cleanreq('rights');
		//process optional host arg if any
		$this->gethostarg();
		//
		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",	$this->xmfield("rights",$rights).$this->xmfield("guid",$guid).
		$this->xmfield("locid",$locid).
		$this->xmfield("ekey",$ekey)));

		//
		// add to the document table

		$timenow=time();
		$insert="INSERT INTO document (guid,creation_time) ".
					"VALUES('$guid',NOW())";
		$this->dbexec($insert,"can not insert into table document - ");
		//
		//pick up the id we just created
		$docid = mysql_insert_id();
		// put an entry in the document location table
		$locid= $this->adddocumentlocation($docid,$this->getnodeid(),$ekey,$intstatus);

		//
		// add to the rights table
		//
		$insert="INSERT INTO rights (account_id,document_id,rights,creation_time) ".
													"VALUES('$mcid','$docid','$rights',NOW())";
		$this->dbexec($insert,"can not insert into table rights - ");
		$rightsid = mysql_insert_id();

    // Add additional rights to the table
    $rights = $_REQUEST["right"];
    if(is_array($rights)) {
      foreach($rights as $rs) {
        $r = split("=",$rs);
        error_log("Granting right ".$r[1]." to account ".$r[0]." for document $docid");
        $this->dbexec("INSERT INTO rights(rights_id, document_id, account_id, rights)
                       VALUES (NULL, $docid,'".$r[0]."','".$r[1]."')", "can not insert into table rights");
      }
    }
		    
		// return outputs
		//docid,rightsid,mcid
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("docid",$docid).		$this->xmfield("locid",$locid).
		$this->xmfield("rightsid",$rightsid).
		$this->xmfield("mcid",$mcid).

		$this->xmfield("status","ok")));
	}
}

//main

$x = new registerDocumentWs();
$x->handlews("registerDocument_Response");



?>
