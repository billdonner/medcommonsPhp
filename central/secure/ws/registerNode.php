<?php
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class registerNodeWs extends dbrestws {

	function xmlbody(){

		// pick up and clean out inputs from the incoming args
		$type = $this->cleanreq('type');
		$hostname = $this->cleanreq('hostname');

		// echo inputs
		//
		$this->xm($this->xmfield ("inputs",	$this->xmfield("type",$type)));

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

    // Look for the node, see if we can find it - currently we match on EITHER ip address OR hostname
    // in the future it is assumed there will be some kind of cert for this.
    $checkNodeSql = "SELECT node_id from node where fixed_ip = '$mushedip' or hostname = '$hostname'";
    $checkNode = $this->dbexec($checkNodeSql,"internal error checking node existence");
    if($checkNode) {
      if($result = mysql_fetch_row($checkNode)) {
        $nodeid = $result[0];
        $this->dbexec("UPDATE node SET hostname = '$hostname', fixed_ip = '$mushedip' WHERE node_id = '$nodeid'","unable to update node details");
      }
    }

    // node not found, create a new one
    if(! $nodeid) {
      $insert="INSERT INTO node (hostname,fixed_ip,node_type,creation_time) ".
         "VALUES('$hostname','$mushedip','$type',NOW())";
        $this->dbexec($insert,"can not insert into table node - ");

        //pick up the id we just created
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
