<?php
require_once "../ws/securewslibdb.inc.php";
require_once "utils.inc.php";
require_once "mc.inc.php";
require_once "../db.inc.php";

/**
 * inheritRightsWs 
 *
 * Duplicates all rights available to the given auth token for accessing document 
 * specified by 'guid' to account toAccount.  No rights are removed from fromAccount.
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
    try {
      // pick up and clean incoming arguments
      $fromAccount=req("fromAccount");
      if(!is_valid_mcid($fromAccount,true))
        throw new Exception("Invalid storage account $fromAccount");

      $toAccount=$_REQUEST['toAccount'];
      if(!is_valid_mcid($toAccount,true))
        throw new Exception("Invalid storage account $toAccount");

      $toAccountType=req('toAccountType');
      if(preg_match("/[0-9a-z]*/",$toAccountType) !== 1)
          throw new Exception("Bad format for toAccountType: $toAccountType");

      $storageAccount=req('storageAccount');
      if(!is_valid_mcid($storageAccount,true))
        throw new Exception("Invalid storage account $storageAccount");

      $guid=req('guid');
      if(preg_match("/[0-9a-z]{40}/",$guid) !== 1)
          throw new Exception("Bad format for guid: $guid");

      $auth = req('auth','');
      $auth = str_replace("token:","",$auth);

      if($storageAccount == "") {
        $storageAccount = "0000000000000000";
      }

      /*
      // Avoid duplicating storage rights: check for storage id
       $allRights = resolve_guid($toAccount, $guid, $auth);

      $hasStorageRights = false;
      if($result && (mysql_num_rows($result)>0)) {
        $hasStorageRights = true;
        error_log("inheritRights:  inheritor has rights already");
      }
      */

      // Find the rights entries to copy
      //
      // NOTE: MySQL, NULL != NULL
      $rights = resolve_guid($fromAccount, $guid, $auth);

      dbg("found ".count($rights)." existing rights for auth=$auth to guid=$guid");
      // Check if the share account type is open id and if so create an external share for it
      $esId = "NULL";
      if($toAccountType == "openid") {
        $this->dbexec("insert into external_share ( es_id, es_identity, es_identity_type )
                       values (NULL, '$toAccount', 'openid')", "Unable to create external share");
        $esId = mysql_insert_id();
        $toAccount = "NULL";
      }
      else
        $toAccount = "'$toAccount'";

      $updateRights = '';
      if($fromAccount != $toAccount) { // don't duplicate rights for the same user.
        foreach($rights as $right) {
          dbg("duplicating rights entry ".$right->rights_id);
          $storageId = $right->storage_account_id ? $right->storage_account_id : 'NULL';
          $docId = $right->document_id ? $right->document_id  : 'NULL';
          $sql = "INSERT INTO rights (account_id,document_id,es_id,storage_account_id,rights) ".
                 "VALUES($toAccount,$docId, $esId, $storageId,'$right->rights')"; // note toAccount is already quoted
          $insertResult = $this->dbexec($sql ,"Unable to add rights");

          if($updateRights!='') {
            $updateRights.=",";
          }
          $updateRights.=$right->rights_id;
        }
     }
      else {
        dbg("inheritRights: toAccount == fromAccount");
      }

      // echo inputs
      $this->xm($this->xmfield ("inputs",
        $this->xmfield("fromAccount",$fromAccount).
        $this->xmfield("toAccount",$toAccount).
        $this->xmfield("guid",$guid)).
        // return outputs
        $this->xmfield ("outputs", $this->xmfield("status","ok").$this->xmfield("inheritedRights", $updateRights)));
    }
    catch(Exception $e) {
        $this->xm($this->xmfield ("outputs", $this->xmfield("status","failed - ".$e->getMessage())));
    }
  }


}

//main
$x = new inheritRightsWs();
$x->handlews("inheritRights_Response");
?>
