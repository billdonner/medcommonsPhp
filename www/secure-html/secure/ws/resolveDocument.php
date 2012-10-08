<?php
require_once "../ws/wslibdb.inc.php";
class resolveDocumentWs extends dbrestws {

	function xmlbody(){
		// pick up and clean incoming arguments
		$guid=$_REQUEST["guid"];
		$accid=$_REQUEST['accountId'];

    // Find the document/rights info
    // NOTE: MySQL, NULL != NULL
    $result = $this->dbexec("select * 
                   from rights r, document d, document_location l 
                   where ((r.document_id = d.id) or (r.storage_account_id = d.storage_account_id))
                     and d.guid = '$guid' 
                     and r.account_id = '$accid'
                     and l.document_id = d.id;", "Unable to query rights");

		// echo inputs
		$this->xm($this->xmfield ("inputs",
		$this->xmfield("guid",$guid).
    $this->xmfield("accid",$accid)));

    // add results
    $docRefs = "";
    if($result) {
      while($rights = mysql_fetch_object($result)) {
        $docRefs.="<docRef><guid>$guid</guid>"
          .$this->xmfield("creationDate",$rights->creation_time)
          .$this->xmfield("location",$rights->node_node_id)
          ."</docRef>\n";
      }

      // return outputs
      $this->xm($this->xmfield ("outputs", $this->xmfield("status","ok").$docRefs));
    }
    else
      $this->xm($this->xmfield ("outputs", $this->xmfield("status","failed")));
  }
}

//main

$x = new resolveDocumentWs();
$x->handlews("resolveDocument_Response");
?>
