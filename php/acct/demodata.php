<?
require_once "testdata.inc.php";
require_once "template.inc.php";
require_once "utils.inc.php";
require_once "demodata_ids.inc.php";

$gw = $GLOBALS['Default_Repository'];

// 11 digit tracking number.  Note that "0" is prepended and this is incremented up as we insert
$trackingNumber = 95150380810;

$jane = new Patient($janesId, $janesEmail, "Jane", "Hernandez", "$demoCcrGuid2","3D Imaging Consult");

$jimsId =   "1106558614028065";
$jimsEmail =   "jjones@medcommons.net";
$jim = new Patient($jimsId, $jimsEmail, "Jim", "Jones");

$jbewell = new Patient($janeBewellId, "jbewell@medcommons.net", "Jane", "Bewell", "fa848f1221ac4124f336984ab1292088ea864849","Update for Diabetes Checkup","f198a83583d6a6428e74acd34592efaff7c59abd");
$staylor = new Patient($stellaId,"spaterson@medcommons.net" , "Stella", "Paterson", "d5af8707f5ab103a6f31c3a48e44f9dc89c5bb26", "For the patient");

$patients = array($jane, $jim, $jbewell, $staylor);

$patientIds = array();
foreach($patients as $p) {
  $patientIds[]=$p->id;
}

$allPatients = implode(",",$patientIds);


// Check if data already exists
$result = mysql_query("select mcid, email from users where mcid = $janesId")
  or err("Unable to check database for existing data");

$dataExists = mysql_num_rows($result) > 0;

if($dataExists)
  $existingEmail = mysql_fetch_object($result)->email;

$quiet = isset($_REQUEST['quiet']);

if($quiet) {
}

  ob_start();
?>

<?if(!$quiet):?>
  <h2>Test Data Management</h2>
<?endif;?>
<?

if(isset($_REQUEST['delete']) || isset($_REQUEST['refresh'])) {
  mysql_query("delete from practiceccrevents where practiceid = $practiceId")
    or warn("Unable to delete RLS events");
  mysql_query("delete from practice where practiceid = $practiceId")
    or warn("Unable to practice");

  mysql_query("delete from practiceccrevents where practiceid = $practice2Id")
    or warn("Unable to delete RLS events for $practice2Id");
  mysql_query("delete from practice where practiceid = $practice2Id")
    or warn("Unable to delete practice for $practice2Id");

  mysql_query("delete from groupadmins where adminaccid = $doctorId")
    or warn("Unable to delete group admin for account $doctorId");

  mysql_query("delete from groupmembers where memberaccid in ($doctorId,$physicianId) or groupinstanceid = $groupInstanceId")
    or warn("Unable to delete group member for account $doctorId or $physicianId");

  mysql_query("delete from groupinstances where groupinstanceid=$groupInstanceId")
    or warn("Unable to delete group instance for group $groupInstanceId");

  mysql_query("delete from groupinstances where groupinstanceid=$group2InstanceId")
    or warn("Unable to delete group instance for group $group2InstanceId");

  mysql_query("delete from document_type where dt_account_id in($doctorId,$physicianId,$allPatients)")
    or warn("Unable to delete documents for $doctorId, or $physicianId, or $allPatients");
  mysql_query("delete from ccrlog where accid in($doctorId,$physicianId,$allPatients)")
    or warn("Unable to delete ccrlog for  $doctorId or $physicianId, or $allPatients");

  mysql_query("delete from users where mcid in ($doctorId,$physicianId,$allPatients)")
    or warn("Unable to delete a user $doctorId or $physicianId or $allPatients");

  mysql_query("delete from rights where storage_account_id in ($janesId,$janeBewellId,$stellaId)")
    or warn("Unable to delete rights to patient accounts");

  foreach($patients as $p) {
    mysql_query("delete from account_rls where ar_accid = '$p->id'")
      or warn("Unable to delete patient rls ".$p->email);
    mysql_query("delete from users where email = '$p->email'")
      or warn("Unable to delete patient by email ".$p->email);
  }

  mysql_query("delete from users where mcid=$groupAcctId")
    or warn("Unable to delete user $groupAcctId");
  mysql_query("delete from users where mcid=$group2AcctId")
    or warn("Unable to delete user $group2AcctId");
    
  mysql_query("delete p.* from prepay_counters p, billacc b where p.billingid = b.billingid and b.accid = '$groupAcctId'")
    or warn("Unable to delete prepay counters for user  $groupAcctId");

  mysql_query("delete b.* from billacc b where b.accid = '$groupAcctId'")
    or warn("Unable to delete billing account for $groupAcctId");
    
  mysql_query("delete from todir where td_owner_accid in ('$doctorId','$physicianId','$groupInstanceId','$groupAcctId')") or warn("Unable to delete users from todir");

  mysql_query("delete tm.* from transfer_message tm, transfer_state ts where tm_transfer_key = ts_key and ts_owner_account_id  in ($groupAcctId, $janesId, $janeBewellId, $stellaId)")
    or warn("Unable to delete transfer messages for $groupAcctId");

  mysql_query("delete tm.* from transfer_message tm 
                 where tm_account_id = $groupAcctId")
    or warn("Unable to delete unlinked transfer messages for $groupAcctId");


  mysql_query("delete from transfer_state where ts_owner_account_id in ($groupAcctId, $janesId, $janeBewellId, $stellaId)")
    or warn("Unable to delete transfer state for $groupAcctId");
?>
<?if(!$quiet):?>
<p>Test Data Deleted!</p>
<form action="?create=true" method="post">
  <p><input type="submit" value="Create Test Data Now"/></p>
</form>
<?endif;?>
<?
}

  if(isset($_REQUEST['create']) || isset($_REQUEST['refresh'])) {
    
    foreach($patients as $p) {

      // Add the user
      insertUser($p->id, $p->first,$p->last, $p->email);

      // Set worklist
      insert("insert into account_rls (ar_accid, ar_rls_url) values ('".$p->id."','".$GLOBALS['Accounts_Url']."ws/R.php?pid=$practiceId')")
        or warn("Error inserting account_rls for ".$p->email);

      // If they have one, add the current ccr
      if($p->ccr != null) {
        insert("INSERT INTO document_type (dt_id,dt_account_id,dt_type,dt_tracking_number,dt_privacy_level,dt_guid,dt_create_date_time,dt_comment) 
          VALUES (NULL,'".$p->id."','CURRENTCCR','','Private','".$p->ccr."',CURRENT_TIMESTAMP,'Demo Current CCR')")
          or warn("Error setting current ccr for ". $p->email);

        // Add ccr to doctors worklist
        $trackingNumber++;
        insert("INSERT INTO practiceccrevents (practiceid,PatientGivenName,PatientFamilyName,PatientIdentifier,PatientIdentifierSource,Guid,Purpose,SenderProviderId,ReceiverProviderId,DOB,CXPServerURL,CXPServerVendor,ViewerURL,Comment,CreationDateTime,ConfirmationCode,RegistrySecret,PatientSex,PatientAge,Status,ViewStatus)
                VALUES (21,'".$p->first."','".$p->last."','".$p->id."','Patient MedCommons Id','".$p->ccr."','".($p->title?$p->title:"")."','idp','idp','16 Jan 1968 05:00:00 GMT','','MedCommons','$gw/access?g=".$p->ccr."','\n            3D Imaging Consult\n            ',1162365858,'0$trackingNumber','','Female','','New','Visible')")
          or warn("Error inserting ".$p->ccr." to worklist");

        // Add ccr log entries
        insert("INSERT INTO ccrlog (id,accid,idp,guid,status,date,src,dest,subject,einfo,tracking,merge_status) 
                VALUES (NULL,".$p->id.",'idp','".$p->ccr."','Complete',{ts '2005-11-01 18:24:18.000'},'UNKNOWN','','CCR',null,'0$trackingNumber',null)")
              or warn("Error inserting ".$p->ccr." to ccrlog for ".$p->email." / ".$p->id);

        if($p->ccr2!=null) {
          $trackingNumber++;
          insert("INSERT INTO ccrlog (id,accid,idp,guid,status,date,src,dest,subject,einfo,tracking,merge_status) 
                  VALUES (NULL,".$p->id.",'idp','".$p->ccr2."','Complete',{ts '2005-11-01 18:24:18.000'},'UNKNOWN','','CCR',null,'0$trackingNumber',null)")
                or warn("Error inserting ".$p->ccr2." to ccrlog for ".$p->email." / ".$p->id);
        }
    }
  }

  // Update jane's role hack so she doesn't get the message about not having anything set
  insert("update users set rolehack='ccr|hm' where mcid in ( $allPatients )")
    or warn("Unable to set patient rolehack flag");

  insertUser($doctorId, "Demo","Doctor", $doctorEmail);

  mysql_query("update users set enable_vouchers = 1, active_group_accid = '$groupAcctId' where mcid = $doctorId")
    or warn("Unable to enable vouchers for demo doctor");

  insertUser($physicianId, "Demo","Physician", $physicianEmail);

  insert("INSERT INTO users ( mcid, email, sha1, server_id, since, first_name, last_name, mobile, smslogin, updatetime, ccrlogupdatetime, chargeclass, rolehack, affiliationgroupid, startparams, stylesheetUrl, picslayout, photoUrl, acctype, persona, validparams)
    VALUES ('$groupAcctId','group-$doctorEmail','".pwhash($groupAcctId)."',1,'2006-09-20 01:19:15','Demo','Group',NULL,NULL,0,1158568912,NULL,'rls',NULL,NULL,NULL,NULL,NULL,'GROUP',NULL,NULL)")
    or warn("Error creating account for Group");

  insert("INSERT INTO groupinstances ( groupinstanceid, grouptypeid, name, groupLogo, adminUrl, memberUrl, parentid, accid, createdatetime) 
          VALUES ($groupInstanceId,0,'Demo Group Worklist','','','',$practiceId,$groupAcctId,'2006-09-20 08:55:45')")
    or warn("Error creating Group");

  insert("INSERT INTO groupmembers VALUES ($groupInstanceId,'$doctorId','')")
    or warn("Error adding $doctorEmail to Group");

  insert("INSERT INTO groupmembers VALUES ($groupInstanceId,'$physicianId','')")
    or warn("Error adding $physicianEmail to Group");

  insert("INSERT INTO groupadmins VALUES ($groupInstanceId,'$doctorId','')")
    or warn("Error adding $doctorEmail as admin for Group");

  insert("INSERT INTO practice VALUES ($practiceId,'Demo Group Worklist',$groupInstanceId,'".$GLOBALS['Accounts_Url']."ws/R.php?pid=$practiceId','http://www.rbh.org.uk/images/why_choose/local_hospital.jpg','$groupAcctId')")
    or warn("Error creating Practice");

  insert("INSERT INTO todir (id,td_owner_accid,td_xid,td_alias,td_contact_list,td_shared_group,td_pin_state,td_contact_accid) VALUES (63,$doctorId,'','$doctorEmail','$doctorEmail',0,0,'$doctorId')")
    or warn("Error inserting $doctorEmail to todir");
  insert("INSERT INTO todir (id,td_owner_accid,td_xid,td_alias,td_contact_list,td_shared_group,td_pin_state,td_contact_accid) VALUES (64,$doctorId,'','$physicianEmail','$physicianEmail',0,0,'$physicianId')")
    or warn("Error inserting $physicianEmail to todir");
  insert("INSERT INTO todir (id,td_owner_accid,td_xid,td_alias,td_contact_list,td_shared_group,td_pin_state,td_contact_accid) 
          VALUES (58,$doctorId,'','Demo Group Worklist','$doctorEmail',0,0,'$groupAcctId')")
    or warn("Error inserting $physicianEmail to todir");
    
    
  // Add some credits for demo doctor
  $billingId = sha1(time());
  insert("insert INTO billacc (billingid, accid) values ('$billingId', '$groupAcctId')");
  insert("insert INTO prepay_counters (billingid, faxin,dicom,acc) values ('$billingId', 10000,10000,10000)");

  // ===== Secondary Group ====
  //
  /*
  insert("INSERT INTO users ( mcid, email, sha1, server_id, since, first_name, last_name, mobile, smslogin, updatetime, ccrlogupdatetime, chargeclass, rolehack, affiliationgroupid, startparams, stylesheetUrl, picslayout, photoUrl, acctype, persona, validparams)
    VALUES ('$group2AcctId','group2-$doctorEmail','".pwhash($group2AcctId)."',1,'2006-09-20 01:19:15','Demo','Group',NULL,NULL,0,1158568912,NULL,'rls',NULL,NULL,NULL,NULL,NULL,'GROUP',NULL,NULL)")
    or warn("Error creating account for Group");

  insert("INSERT INTO groupinstances ( groupinstanceid, grouptypeid, name, groupLogo, adminUrl, memberUrl, parentid, accid, createdatetime) 
          VALUES ($group2InstanceId,0,'$group2Name','','','',$practice2Id,$group2AcctId,'2006-09-20 08:55:45')")
    or warn("Error creating Group");

  insert("INSERT INTO groupmembers VALUES ($group2InstanceId,'$doctorId','')")
    or warn("Error adding $doctorEmail to Group $group2Name");

  insert("INSERT INTO groupadmins VALUES ($group2InstanceId,'$doctorId','')")
    or warn("Error adding $doctorEmail as admin for Group $group2Name");

  insert("INSERT INTO practice VALUES ($practice2Id,'$group2Name Worklist',$group2InstanceId,'".$GLOBALS['Accounts_Url']."ws/R.php?pid=$practice2Id','http://www.rbh.org.uk/images/why_choose/local_hospital.jpg','$group2AcctId')")
    or warn("Error creating Practice");

  insert("INSERT INTO todir (id,groupid,xid,alias,contactlist,sharedgroup,pinstate,accid) VALUES (66,$group2InstanceId,'','$doctorEmail','$doctorEmail',0,0,'$doctorId')")
    or warn("Error inserting $doctorEmail to todir");
   */

  // ccrlog / ccrevents
  /*mysql_query("delete from ccrlog where accid in($janesId,$doctorId)")
    or warn("Unable to delete ccrlog for user $janesId or $doctorId");
  mysql_query("delete from practiceccrevents where practiceid = $practiceId")
    or warn("Unable to delete RLS events");
   */

  insert("INSERT INTO ccrlog (id,accid,idp,guid,status,date,src,dest,subject,einfo,tracking,merge_status) VALUES (NULL,$doctorId,'idp','$demoCcrGuid','Complete',{ts '2005-11-01 18:24:18.000'},'UNKNOWN','','DICOM Import',null,'095150380810',null)")
    or warn("Error inserting $demoCcrGuid to ccrlog for $doctorEmail / $doctorId");
  insert("INSERT INTO ccrlog (id,accid,idp,guid,status,date,src,dest,subject,einfo,tracking,merge_status) VALUES (NULL,$janesId,'idp','$demoCcrGuid','Complete',{ts '2005-11-01 18:24:18.000'},'UNKNOWN','','DICOM Import',null,'095150380811',null)")
    or warn("Error inserting $demoCcrGuid to ccrlog for $janesEmail / $janesId");
  insert("INSERT INTO ccrlog (id,accid,idp,guid,status,date,src,dest,subject,einfo,tracking,merge_status) 
          VALUES (NULL,$janesId,'idp','$demoCcrGuid2','Complete',{ts '2006-11-23 17:44:18.000'},'UNKNOWN','','DICOM Import',null,'095150380812',null)")
    or warn("Error inserting $demoCcrGuid2 to ccrlog for $janesEmail / $janesId");

  $rightsResult = file_get_contents($GLOBALS['Commons_Url']."/demodata.php");
  if($rightsResult==false) {
    err("Unable to grant rights for $janesEmail to access current ccr $demoCcrGuid");
  }

  $gwResetURL = gpath('Default_Repository')."/ResetDemoData.action";
  $gwResult = file_get_contents($gwResetURL);
  if($gwResult==false) {
    err("Unable to reset demo data on gateawy using ".$gwResetURL);
  }
  if(!$quiet) {
    echo "<div style='border: solid 1px #8a6; font-family: arial; font-size: 9px;'><ul>$insertBuffer</ul></div>";
  }
?>
<?if(!$quiet):?>
<p>Test Data Created!</p>
  <form action="?delete=true" method="post">
    <p><input type="submit" value="Delete Test Data Now"/></p>
  </form>
<?endif;?>
<?
}

if(!isset($_REQUEST['create']) && !isset($_REQUEST['refresh']) && !isset($_REQUEST['delete'])) {
?>
<?if(!$quiet):?>
  <p>This page creates the standard MedCommons Test Data for your system.</p>
<?if($dataExists) {
  echo "<p><b>Note: an existing user $janesId ($existingEmail) was found on this system</b></p>";
}?>
  <form action="?create=true" method="post">
    <p><input type="submit" value="Create Test Data Now"/></p>
  </form>
  <form action="?delete=true" method="post">
    <p><input type="submit" value="Delete Test Data Now"/></p>
  </form>
<?endif;?>
<?
}
if($quiet):?>
<p>Demonstration data has been reset to defaults.</p>
<?endif;?>
<?
  $contents = ob_get_contents();
  ob_end_clean();
  $tpl = req('tpl','layout');
  echo template("$tpl.tpl.php")->set("content",$contents)->set("head","")->set("title","Test Data Management")->fetch();
?>
