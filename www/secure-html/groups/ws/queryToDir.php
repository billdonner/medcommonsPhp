<?php
/**
* find records from the todir moved into /groups on 14 aug 06
*/
require_once "wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class queryToDirWs extends dbrestws {
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
		if ($where!='')	$where = "where $where";
			$timenow=time();
		$count = 0; $bulk='';
		$query="SELECT * FROM todir $where";
		$result = $this->dbexec($query,"can not select table todir - ");
		while (true) {
		$l  = mysql_fetch_assoc($result);
		if ($l===false) break;
		$bulk.= $this->xmfield("todir_entry",
		$this->xmfield ("ctx",$l['group']).
		$this->xmfield ("xid",$l['xid']).
		$this->xmfield ("alias",$l['alias']).
		$this->xmfield ("accid",$l['accid']).
		$this->xmfield ("sharedgroup",$l['sharedgroup']).
		$this->xmfield ("pinstate",$l['pinstate']).
		$this->xmfield ("contact",$l['contactlist']));
	
		$count++;	
		}
	
		$this->xm($this->xmfield ("outputs",$bulk.$this->xmfield("status","ok rows=$count")));
	}

}

//main

$x = new queryToDirWs();
$x->handlews("queryToDir_Response");
?>