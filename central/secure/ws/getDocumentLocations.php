<?php
/*
getDocumentLocations

DocumentLocation[] getDocumentLocationss(java.lang.String guid,
                                       java.lang.String nodeName)

    Returns an array of DocumentLocation objects for the specified guid. If the nodeName is null then all locations are returned; otherwise only the ones matching the specified nodeName are returned.

    Parameters:
        guid - 
        nodeName - 
    Returns:


*/

require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class getDocumentLocationsWs extends dbrestws {


	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$guid = $this->cleanreq('guid');
		$node = $this->cleanreq('node');
	
		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",
		$this->xmfield("node",$node).
		$this->xmfield("guid",$guid)));
				
		$docid = finddocument($guid);
		if ($docid=="") {$this->xm($this->xmfield("status","can't find $guid ")); exit;}
	
		
		$select="SELECT * FROM document_location WHERE (document_id = '$docid')";
		if ($node!='') $select .= " AND (node_node_id = '$node')";
		
		$result = $this->dbexec($select,"can not select from table document_location - ");
		$str = "";
		while ($dlobj = mysql_fetch_object($result)){
				$str .= "<docloc><guid>".$dlobj->guid."</guid><ekey>".$dlobj->encrypted_key.
				"</ekey><node>".$dlobj->node_node_id."</node></docloc>";

		}
		// return outputs
		$status = "ok";
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("docid",$docid).
		$this->xmfield("doclocs",$str).
		$this->xmfield("status",$status)));
	}
}

//main

$x = new getDocumentLocationsWs();
$x->handlews("getDocumentLocations_Response");
?>
