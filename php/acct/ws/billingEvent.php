<?php
require_once "wslibdb.inc.php";
/**
 * billingEventWs 
 *
 * Registers a billing event with the account server
 *
 * Inputs:
 *    accountId - account id to set the current ccr for
 *    type  - type of event, one of predefined event types: INBOUND_FAX
 *    reference - guid / tracking number / other reference for the transaction
 *    count - optional amount of the billing increment, for INBOUND_FAX, unit is pages
 *    description - short human readable description
 */
class billingEventWs extends dbrestws {
	function xmlbody(){
    $accid = $_REQUEST['accountId'];
    $type = $_REQUEST['type'];
    $reference = $_REQUEST['reference'];
    $desc  = $_REQUEST['description'];
    $count  = isset($_REQUEST['count']) ? $_REQUEST['count'] : null ; // page count when type == INBOUND_FAX

    error_log("Received billing event $type for account $accid");

    $this->xm($this->xmfield ("outputs",$this->xmfield("status","ok")));
	}
}

//main

$x = new billingEventWs();
$x->handlews("billingEvent_Response");

?>
