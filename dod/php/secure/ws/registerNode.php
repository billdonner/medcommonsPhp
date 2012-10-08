<?php
require_once "../ws/securewslibdb.inc.php";
require_once "utils.inc.php";

// see spec at ../ws/wsspec.html
class registerNodeWs extends dbrestws {

	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$type = $this->cleanreq('type');
		$hostname = $this->cleanreq('hostname');
		$key = $this->cleanreq('key');
		$expectedNodeId = $this->cleanreq('nodeId');

		// echo inputs
		//
		$this->xm($this->xmfield ("inputs",	$this->xmfield("type",$type)));

    if(preg_match("/^[0-9]*$/",$expectedNodeId)!==1) {
      $this->xm($this->xmfield("status","failed").$this->xmfield("error","bad node id"));
    }


		// get the ipaddress and port of remote caller
		$ip = $_SERVER['REMOTE_ADDR'];

    // mushedip is used because we (mistakenly?) made the ip address
    // column in the db an integer.  This causes mysql to do some
    // strange things to turn ip addresses "127.0.0.1" into integers
    // which makes them no longer unique.  Hence we "mush" the ip.
    // We should probably just make the column some other data type
    // or maybe convert the ip address to it's full decimal form
    $mushedip = $this->muship($ip);

		// add to the node table

		$timenow=time();

    $nodeid = "";
    $nodeFound = false;
    $key = req('key');

    // See if we have encountered this node yet
    $result = $this->dbexec("select * from node where client_key = '".mysql_real_escape_string($key)."' or node_id = $expectedNodeId"," - failed to select from node table");
    if(mysql_num_rows($result)>0) {
      $node = mysql_fetch_object($result);
      $nodeid = $node->node_id;
    }
    else { // not found.  Make a new row
      $result = $this->dbexec("insert into node (node_id, hostname, fixed_ip  , node_type , client_key)
          values (NULL, '".mysql_real_escape_string($hostname)."', $mushedip, 0, '".mysql_real_escape_string($key)."')", " - failed to insert new node");
      
      $nodeid = mysql_insert_id();
    }

		// return outputs
		//docid,rightsid,mcid
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("ip",$ip).
		$this->xmfield("nodeid",$nodeid).
		$this->xmfield("status","ok")));
	}
}

//main

$x = new registerNodeWs();
$x->handlews("registerNode_Response");



?>
