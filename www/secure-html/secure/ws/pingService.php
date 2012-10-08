<?php
require_once "../ws/wslib.inc.php";
// send back a response , turning around a user supplied string
class pingws extends restws {

	function xmlbody(){
		//
		// echo inputs
		//

		$this->xm($this->xmfield ("inputs",
		$this->xmfield("a",$this->cleanreq('a'))));

		//
		// return outputs
		//
		$this->xm($this->xmfield ("outputs",
		$this->xmfield("status","ok")));
	}
}

//main

$x = new pingws();
$x->handlews("ping_Response");



?>