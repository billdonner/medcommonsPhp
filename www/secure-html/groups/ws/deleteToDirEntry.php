<?php
/**
* Delete an entry from the ToDir - moved into /groups on 14 aug 06
*
* the accid, if supplied, is purportedly the id of the administrator who made this entry
*/
require_once "wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class delToDirEntryWs extends dbrestws {
	function xmlbody(){
		$where = '';
		// pick up and clean out inputs from the incoming args
		$xid = $this->cleanreq('xid');
		if ($xid!='') {
			if ($where!='') $where.=' and ';
			$where.= "('$xid'=xid)";
		}
		$ctx = $this->cleanreq('ctx');
		if ($ctx!='') {
			if ($where!='') $where.=' and ';
			$where.= "('$ctx'=groupid)";
		}
		$alias = $this->cleanreq('alias');
		if ($alias!='') {
			if ($where!='') $where.=' and ';
			$where.= "('$alias'=alias)";
		}
		$accid = $this->cleanreq('accid'); //accid of administrator making this entry
		if ($accid!='') {
			if ($where!='') $where.=' and ';
			$where.= "('$accid'=accid)";
		}
		if ($where=='')		
		$this->xm($this->xmfield ("outputs",$this->xmfield("status","noparams")));
		else {
			$timenow=time();

			$delete="Delete from todir where $where";
			$this->dbexec($delete,"can not delete table todir - ");
			$count =mysql_affected_rows();
			$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok rows $count")));
		}
	}
}

//main

$x = new delToDirEntryWs();
$x->handlews("addToDirEntry_Response");
?>