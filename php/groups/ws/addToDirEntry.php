<?php
/**
* Adds a new entry to the ToDir moved into /groups on 14 aug 06
*
* the accid, if supplied, is purportedly the id of the administrator making this entry
*/
require_once "wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class addToDirEntryWs extends dbrestws {
	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$xid = $this->cleanreq('xid');

		$ctx = $this->cleanreq('ctx');

		$alias = $this->cleanreq('alias');

		$contact = $this->cleanreq('contact');
		
		$accid = $this->cleanreq('accid'); //accid of administrator making this entry
		
		$shared = $this->cleanreq('shared'); //accid of administrator making this entry
				
		$pin = $this->cleanreq('pin'); //accid of administrator making this entry
		$timenow=time();

		$insert="INSERT INTO todir(xid,groupid,alias,contactlist,accid,sharedgroup,pinstate)
				VALUES('$xid','$ctx','$alias','$contact', '$accid','$shared','$pinstate')";
		$this->dbexec($insert,"can not insert into table todir - ");
		$this->xm($this->xmfield ("outputs",$this->xmfield("status","ok $ob")));
	}
}

//main

$x = new addToDirEntryWs();
$x->handlews("addToDirEntry_Response");
?>