<?php
require_once "../ws/wslibdb.inc.php";
class grantAccountAccessWs extends dbrestws {

	function xmlbody(){
		// pick up and clean incoming arguments
		$accessTo=$_REQUEST["accessTo"];
		$accessBy=$_REQUEST['accessBy'];
		$rights=$_REQUEST['rights'];

    // Find the document/rights info
    // NOTE: MySQL, NULL != NULL
    $result = $this->dbexec(
      "INSERT INTO rights (account_id,storage_account_id,rights,creation_time) ".
      "VALUES('$accessBy','$accessTo','$rights',NOW())","Unable to insert rights");

		// echo inputs
		$this->xm($this->xmfield ("inputs",
      $this->xmfield("accessTo",$accessTo).
      $this->xmfield("rights",$rights).
      $this->xmfield("accessBy",$accessBy)).
      // return outputs
      $this->xmfield ("outputs", $this->xmfield("status","ok")));
  }
}

//main

$x = new grantAccountAccessWs();
$x->handlews("grantAccountAccess_Response");
?>
