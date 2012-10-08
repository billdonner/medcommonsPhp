<?php
require_once "wslibdb.inc.php";
/**
 * querySettingsWs 
 *
 * Returns account settings for the requested account.
 *
 * Inputs:
 *    accid - account id to check
 *
 * @author ssadedin@medcommons.net
 */

// NOTE: the xml below is probably going to end up needing to be escaped.
// In fact this should probably happen for every XML field.  Because the PHP
// is not regression tested I'm not brave enough to just plunk this into the higher
// level code as any places that are already escaping their xml will then break.
function xmlentities($string)
{
   return str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string );
}

class querySettingsWs extends dbrestws {
	function xmlbody(){
		// pick up and clean out inputs from the incoming args
		$accid = $this->cleanreq('accid');
    $sql = "select g.*, p.practiceRlsUrl as \"practiceRlsUrl\" 
            from groupmembers u, groupinstances g
            left join practice p on p.providergroupid=g.groupinstanceid
            where u.groupinstanceid = g.groupinstanceid and u.memberaccid = '$accid'";
		$result = $this->dbexec($sql,"can not select from users - ".mysql_error());
    // if not found in account table, look in group table
    if(mysql_num_rows($result)==0) {
      $sql = "select g.* from groupinstances g where g.accid = '$accid'";
      $result = $this->dbexec($sql,"can not select from groupinstances - ".mysql_error());
    }

    if($result) {
      $settings = mysql_fetch_object($result);
    }

    $result = $this->dbexec("select dt_guid from document_type where dt_type='CURRENTCCR' and dt_account_id='$accid' order by dt_create_date_time desc limit 1","can not select from document_type");
    if($result) {
      $row = mysql_fetch_array($result);
      if($row) {
        $settings->currentCcrGuid = $row[0];
      }
    }

    if($settings) {
      $this->xm(
        $this->xmfield ("outputs",
          $this->xmfield("status","ok").
          $this->xmfield("groupInstanceId",$settings->groupinstanceid). // For now first group only TODO: change protocol to return all groups
          $this->xmfield("groupAccountId",$settings->accid). // For now first group only TODO: change protocol to return all groups
          $this->xmfield("groupName",$settings->name). 
          $this->xmfield("registry",$settings->practiceRlsUrl).
          $this->xmfield("directory","" /* TODO: Need ToDir here */).
          $this->xmfield("currentCcrGuid",$settings->currentCcrGuid).
          $this->xmfield("creationRights", $this->xmfield("accountId",$settings->accid)) // send back group account for access rights
        )
      );
    }
    else {
      $this->xm(
        $this->xmfield ("outputs",
          $this->xmfield("status","failed").
          $this->xmfield("message","account record or settings not found")
        )
      );
    }
	}
}

// main
$x = new querySettingsWs();
$x->handlews("querySettings_Response");
?>
