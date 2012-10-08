<?php
require_once "../ws/securewslibdb.inc.php";

/**
 * getPermissions 
 *
 * returns the string of permissions representing the access available to the
 * given authentication context to the requested account.
 * 
 * @param auth - authentication token identifying authentication context
 * @param toAccount - account to which permissions are being queried
 *
 * @author ssadedin@medcommons.net
 */
class getPermissionsWs extends dbrestws {

	function xmlbody(){

		// pick up and clean incoming arguments
		$toAccount=$_REQUEST['toAccount'];
		$auth=$_REQUEST['auth'];
    $rights = "";
    $status = "ok";

    try {
      $rights = $this->get_authorized_rights($auth, $toAccount);
    }
    catch(Exception $e) {
      error_log("Failed to get rights for auth $auth and account $toAccount : ".$e->getMessage());
      $status = "failed";
      $rights = "";
    }
 
		// echo inputs
		$this->xm($this->xmfield ("inputs",
      $this->xmfield("toAccount",$toAccount)).
      // return outputs
      $this->xmfield ("outputs", $this->xmfield("status","$status").$this->xmfield("rights", $rights)));
  }
}

//main
$x = new getPermissionsWs();
$x->handlews("getPermissions_Response");
?>
