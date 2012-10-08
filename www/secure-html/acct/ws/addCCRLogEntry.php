<?php
/**
 * Adds a new entry in the CCR Log
 *
 * ssadedin: modified 8/31/06 to support multiple accounts in one call
 */
require_once "wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class addCCRLogEntryWs extends dbrestws {

  function xmlbody(){

    // pick up and clean out inputs from the incoming args
    $date = $this->cleanreq('date');
    $idp = $this->cleanreq('idp');
    $accid = $this->cleanreq('accid');
    $guid = $this->cleanreq('guid');
    $tracking  = $this->cleanreq('tracking');
    $from = $this->cleanreq('from');
    $to = $this->cleanreq('to');
    $subject = $this->cleanreq('subject');
    $status = $this->cleanreq('status');
    $timenow=time();                        

    //
    // add to the CCRLogEntry table
    $accids = explode(",",$accid);
    foreach($accids as $a) {
      $insert="INSERT INTO ccrlog(accid, guid,tracking,status, date ,src, dest,subject,idp) ".
            "VALUES('$a','$guid','$tracking','$status', NOW(),'$from','$to','$subject','$idp')";
      $ob= "UPDATE users SET  ccrlogupdatetime = '$timenow' where (mcid = '$a')";
      $this->dbexec($ob,"can not update users in addCCRLogEntry");
      $this->dbexec($insert,"can not insert into table ccrlog - ");
    } 

    $this->xm($this->xmfield ("outputs", $this->xmfield("status","ok $ob")));
  }
}

//main

$x = new addCCRLogEntryWs();
$x->handlews("addCCRLogEntry_Response");
?>
