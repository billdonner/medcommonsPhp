<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html

class resetdbWs extends dbrestws {
	
function empty_table($tab)
{ 
	$del = "DELETE FROM $tab;";
	$result = $this->dbexec($del,"can not delete from table $tab - ");
	if ($result===false) return ($this->xmfield($tab, $result));
	return ($this->xmfield($tab,"ok"));
}
	
	function xmlbody(){
				
		//
		// return outputs
		//
		$this->xm($this->xmfield ("outputs",
		$this->empty_table("rights").
		$this->empty_table("tracking_number").
		$this->empty_table("document").
		$this->empty_table("document_location").
		$this->xmfield("status",$status)));
		
	}
}

//main

$x = new resetdbWs();
$x->handlews("reset_database_Response");



?>