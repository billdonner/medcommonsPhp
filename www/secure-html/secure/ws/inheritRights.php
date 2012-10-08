<?php
require_once "../ws/wslibdb.inc.php";

/**
 * inheritRightsWs 
 *
 * Duplicates all rights available to fromAccount for accessing document specified by 'guid'
 * to account toAccount.  No rights are removed from fromAccount.
 * <p/>
 * Note: if fromAccount has global access rights to the storage id associated with 'guid' then
 * this right will also be duplicated, thus granting toAccount the same global access
 * to all documents stored under this storage id.
 * 
 * @param fromAccount - account currently having rights
 * @param toAccount   - account which should gain rights
 * @param storageAccount - account under which document is stored
 * @param guid        - document
 *
 * @author ssadedin@medcommons.net
 */
class inheritRightsWs extends dbrestws {

	function xmlbody(){
		// pick up and clean incoming arguments
		$fromAccount=$_REQUEST["fromAccount"];
		$toAccount=$_REQUEST['toAccount'];
		$storageAccount=$_REQUEST['storageAccount'];
		$guid=$_REQUEST['guid'];


    // Avoid duplicating storage rights: check for storage id
    $result = $this->dbexec("select r.* 
                   from rights r, document d
                   where (r.storage_account_id = d.storage_account_id)
                     and d.guid = '$guid' 
                     and d.storage_account_id = '$storageAccount' 
                     and r.account_id = '$toAccount'", "Unable to query rights");

    $hasStorageRights = false;
    if($result && (mysql_num_rows($result)>0)) {
      $hasStorageRights = true;
      error_log("inheritRights:  inheritor has rights already");
    }
    
    // Find the rights entries to copy
    //
    // NOTE: MySQL, NULL != NULL
    $result = $this->dbexec("select r.* 
                   from rights r, document d
                   where ((r.document_id = d.id) or (r.storage_account_id = d.storage_account_id))
                     and d.guid = '$guid' 
                     and r.account_id = '$fromAccount'", "Unable to query rights");

    $updateRights = '';
    if($fromAccount != $toAccount) { // don't duplicate rights for the same user.
      while ($right = mysql_fetch_object($result)) {
        $storageId = $right->storage_account_id ? $right->storage_account_id : 'NULL';
        $docId = $right->document_id ? $right->document_id  : 'NULL';
        $sql = "INSERT INTO rights (account_id,document_id,storage_account_id,rights) ".
               "VALUES('$toAccount',$docId, $storageId,'$right->rights')";
        $insertResult = $this->dbexec($sql ,"Unable to add rights");

        if($updateRights!='') {
          $updateRights.=",";
        }
        $updateRights.=$rights->rights_id;
      }
   }
    else {
      error_log("inheritRights: toAccount == fromAccount");
    }

		// echo inputs
		$this->xm($this->xmfield ("inputs",
      $this->xmfield("fromAccount",$fromAccount).
      $this->xmfield("toAccount",$toAccount).
      $this->xmfield("guid",$guid)).
      // return outputs
      $this->xmfield ("outputs", $this->xmfield("status","ok").$this->xmfield("inheritedRights", $updateRights)));
  }
}

//main
$x = new inheritRightsWs();
$x->handlews("inheritRights_Response");
?>
