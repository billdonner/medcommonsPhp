<?
/*
 * Test Data Creation Script
 *
 * Creates / Deletes a standard set of test data useful for unit tests.
 *
 * The standard set of data consists of two practices and a number of doctors and a patient.
 *
 *     +----------------------------+
 *     | GROUP 1 - Good Doctors     |
 *     |                            |
 *     |       doctor1              |
 *     |       doctor2              |
 *     |      +------------+--------+------+
 *     |      |doctor3              |      |
 *     +------+------------+--------+      |
 *            |   GROUP 2 - Better Doctors |
 *            |                            |
 *            |                            |
 *            +----------------------------+
 */

require_once "utils.inc.php";
require_once "testdata.inc.php";
require_once "testdata_ids.inc.php";

global $doctorId , $doctorEmail , $doctor2Email , $doctor3Email , $user1Email , $user2Email , $practiceId , 
  $practice2Id , $groupInstanceId , $groupAcctId, $group2InstanceId , $group2AcctId, $group2Name, 
  $doctor2Id , $doctor3Id , $user1Id , $user2Id , $coverSheetId;

// Check if data already exists
$result = mysql_query("select mcid, email from users where mcid = $doctorId")
  or err("Unable to check database for existing data");

$dataExists = mysql_num_rows($result) > 0;

if($dataExists)
  $existingEmail = mysql_fetch_object($result)->email;
  $render = true;
?><html>
<body style="font-family: arial;">
  <h2>Test Data Creation Page</h2>
<?
if(isset($_REQUEST['delete']) || isset($_REQUEST['refresh'])) {
  $render = false;
  mysql_query("delete from practiceccrevents where practiceid = $practiceId")
    or warn("Unable to delete RLS events");
  mysql_query("delete from practiceccrevents where practiceid = $practice2Id")
    or warn("Unable to delete RLS events for practice $practice2Id");
  mysql_query("delete from practice where practiceid = $practiceId")
    or warn("Unable to practice");
  mysql_query("delete from practice where practiceid = $practice2Id")
    or warn("Unable to delete practice $practice2Id");
  mysql_query("delete from groupadmins where adminaccid = $doctorId")
    or warn("Unable to delete group admin for account $doctorId");
  mysql_query("delete from groupadmins where adminaccid = $doctor3Id")
    or warn("Unable to delete group admin for account $doctor3Id");
  mysql_query("delete from groupmembers where memberaccid = $doctorId")
    or warn("Unable to delete group member for account $doctorId");
  mysql_query("delete from groupmembers where memberaccid = $doctor2Id")
    or warn("Unable to delete group member for account $doctorId");
  mysql_query("delete from groupmembers where memberaccid = $doctor3Id")
    or warn("Unable to delete group member for account $doctorId");
  mysql_query("delete from groupinstances where groupinstanceid=$groupInstanceId")
    or warn("Unable to delete group instance for group $groupInstanceId");
  mysql_query("delete from groupinstances where groupinstanceid=$group2InstanceId")
    or warn("Unable to delete group instance for group $group2InstanceId");
  mysql_query("delete from document_type where dt_account_id=$user2Id")
    or warn("Unable to delete documents for user $user2Id");
  mysql_query("delete from ccrlog where accid=$user2Id")
    or warn("Unable to delete ccrlog for user $user2Id");
  mysql_query("delete from ccrlog where accid=$user1Id")
    or warn("Unable to delete ccrlog for user $user1Id");
  mysql_query("delete from document_type where dt_account_id=$user1Id")
    or warn("Unable to delete documents for user $user1Id");
  mysql_query("delete from document_type where dt_account_id=$doctorId")
    or warn("Unable to delete documents for user $doctorId");

  mysql_query("delete from users where mcid=$doctorId")
    or warn("Unable to delete user $doctorId");
  mysql_query("delete from users where mcid=$groupAcctId")
    or warn("Unable to delete user $groupAcctId");
  mysql_query("delete from users where mcid=$group2AcctId")
    or warn("Unable to delete user $group2AcctId");
  mysql_query("delete from users where mcid=$doctor2Id")
    or warn("Unable to delete user $doctor2Id");
  mysql_query("delete from users where mcid=$doctor3Id")
    or warn("Unable to delete user $doctor3Id");
  mysql_query("delete from users where mcid=$user1Id")
    or warn("Unable to delete user $user1Id");
  mysql_query("delete from users where mcid=$user2Id")
    or warn("Unable to delete user $user2Id");

  mysql_query("delete from users where email='nonexistant@medcommons.net'")
    or warn("Unable to delete user nonexistant@medcommons.net");

  // some tests add user 1 to a group.
  mysql_query("delete  from groupadmins where adminaccid = $user1Id")
    or warn("Unable to delete user $user1Id from group admin");
  mysql_query("delete  from groupmembers where memberaccid = $user1Id")
    or warn("Unable to delete user $user1Id from groups");
  mysql_query("delete from cover where cover_id = $coverSheetId")
    or warn("Unable to delete fax cover sheet id = $coverSheetId");

  mysql_query("delete p.* from prepay_counters p, billacc b where p.billingid = b.billingid and b.accid = '$groupAcctId'")
    or warn("Unable to delete prepay counters for user  $groupAcctId");

  mysql_query("delete b.* from billacc b where b.accid = '$groupAcctId'")
    or warn("Unable to delete billing account for $groupAcctId");

  // Add some credits for test group 
  $billingId = sha1(time());
  insert("insert INTO billacc (billingid, accid) values ('$billingId', '$groupAcctId')");
  insert("insert INTO prepay_counters (billingid, faxin,dicom,acc) values ('$billingId', 10000,10000,10000)");

  mysql_query("delete from todir where td_owner_accid in ('$doctor3Id','$doctor2Id', '$doctorId', '$groupAcctId', '$group2AcctId',  '32')") or warn("Unable to delete users from todir");


  try {
    $gwResetURL = gpath('Default_Repository')."/ResetTestData.action";
    $gwResult = get_url($gwResetURL);
  }
  catch(Exception $ex) {
      err("Unable to reset demo data on gateawy using ".$gwResetURL.": ".$ex->getMessage());
  }

?>
<p>Test Data Deleted!</p>
<form action="?create=true" method="post">
  <p><input type="submit" value="Create Test Data Now"/></p>
</form>
<?
}

if(isset($_REQUEST['create']) || isset($_REQUEST['refresh'])) {
  $render = false;
  insertDoctor($doctorId, "User", "$doctorEmail");
  insertDoctor($doctor2Id, "Two", "$doctor2Email");
  insertDoctor($doctor3Id, "Three", "$doctor3Email");

  insert("update users set active_group_accid = '$groupAcctId' where mcid in ($doctorId, $doctor2Id)")
    or warn("Cannot update doctors with active group");

  insert("update users set active_group_accid = '$group2AcctId' where mcid in ($doctor3Id)")
    or warn("Cannot update doctors with active group");

  // ==== Primary Group ====
  insert("INSERT INTO users ( mcid, email, sha1, server_id, since, first_name, last_name, mobile, smslogin, updatetime, ccrlogupdatetime, chargeclass, rolehack, affiliationgroupid, startparams, stylesheetUrl, picslayout, photoUrl, acctype, persona, validparams) VALUES ('$groupAcctId','doctors@medcommons.net','".pwhash($groupAcctId)."',1,'2006-09-20 01:19:15','Doctor','Group',NULL,NULL,0,1158568912,NULL,'rls',NULL,NULL,NULL,NULL,NULL,'GROUP',NULL,NULL)")
    or warn("Error creating account for Good Doctors Group");

  insert("INSERT INTO groupinstances ( groupinstanceid, grouptypeid, name, groupLogo, adminUrl, memberUrl, parentid, accid, createdatetime) 
          VALUES ($groupInstanceId,0,'Good Doctors','','','',$practiceId,$groupAcctId,'2006-09-20 08:55:45')")
    or warn("Error creating Good Doctors Group");

  insert("INSERT INTO groupmembers VALUES ($groupInstanceId,'$doctorId','')")
    or warn("Error adding $doctorEmail to Good Doctors Group");

  insert("INSERT INTO groupmembers VALUES ($groupInstanceId,'$doctor2Id','')")
    or warn("Error adding $doctor3Email to Good Doctors Group");

  /*
  insert("INSERT INTO groupmembers VALUES ($groupInstanceId,'$doctor3Id','')")
    or warn("Error adding $doctor3Email to Good Doctors Group");
   */

  insert("INSERT INTO groupadmins VALUES ($groupInstanceId,'$doctorId','')")
    or warn("Error adding $doctorEmail as admin for Good Doctors Group");

  insert("INSERT INTO practice VALUES ($practiceId,'Good Doctors Practice',$groupInstanceId,'".$GLOBALS['Accounts_Url']."ws/R.php?pid=$practiceId','http://www.rbh.org.uk/images/why_choose/local_hospital.jpg','$groupAcctId')")
    or warn("Error creating Good Doctors Practice");

  insert("INSERT INTO users ( mcid, email, sha1, server_id, since, first_name, last_name, mobile, smslogin, updatetime, ccrlogupdatetime, chargeclass, rolehack, affiliationgroupid, startparams, stylesheetUrl, picslayout, photoUrl, acctype, persona, validparams) VALUES ('$user1Id','$user1Email','".pwhash($user1Id)."',1,'2006-09-20 01:19:15','User','One',NULL,NULL,0,1158568912,NULL,'full',NULL,NULL,NULL,NULL,NULL,'USER',NULL,NULL)")
    or warn("Error creating user $user1Email");

  insert("INSERT INTO users ( mcid, email, sha1, server_id, since, first_name, last_name, mobile, smslogin, updatetime, ccrlogupdatetime, chargeclass, rolehack, affiliationgroupid, startparams, stylesheetUrl, picslayout, photoUrl, acctype, persona, validparams) VALUES ('$user2Id','$user2Email','".pwhash($user2Id)."',1,'2006-09-20 01:19:15','User','Two',NULL,NULL,0,1158568912,NULL,'full',NULL,NULL,NULL,NULL,NULL,'USER',NULL,NULL)")
    or warn("Error creating user $user2Email");

  insert("INSERT INTO todir (id,td_owner_accid,td_xid,td_alias,td_contact_list,td_shared_group,td_pin_state,td_contact_accid) VALUES (60, '$doctorId','','$doctor3Email','$doctor3Email',0,0,'$doctor3Id')")
    or warn("Error inserting $doctor3Email to todir");
  insert("INSERT INTO todir (id,td_owner_accid,td_xid,td_alias,td_contact_list,td_shared_group,td_pin_state,td_contact_accid) VALUES (61, '$doctorId','','$doctor2Email','$doctor2Email',0,0,'$doctor2Id')")
    or warn("Error inserting $doctor2Email to todir");
  insert("INSERT INTO todir (id,td_owner_accid,td_xid,td_alias,td_contact_list,td_shared_group,td_pin_state,td_contact_accid) VALUES (62, '$doctorId','','$doctorEmail','$doctorEmail',0,0,'$doctorId')")
    or warn("Error inserting $doctorEmail to todir");

  // ==== Secondary Group ====

  insert("INSERT INTO users ( mcid, email, sha1, server_id, since, first_name, last_name, mobile,
    smslogin, updatetime, ccrlogupdatetime, chargeclass, rolehack, 
    affiliationgroupid, startparams, stylesheetUrl, picslayout, photoUrl,
    acctype, persona, validparams) VALUES ('$group2AcctId','doctors2@medcommons.net','".pwhash($group2AcctId)."',1,'2006-09-20 01:19:15','Doctor','Group',NULL,NULL,0,1158568912,NULL,'rls',NULL,NULL,NULL,NULL,NULL,'GROUP',NULL,NULL)")
    or warn("Error creating account for $group2Name group");

  insert("INSERT INTO groupinstances ( groupinstanceid, grouptypeid, name, groupLogo, adminUrl, memberUrl, parentid, accid, createdatetime) 
          VALUES ($group2InstanceId,0,'$group2Name','','','',$practice2Id,$group2AcctId,'2006-09-20 08:55:45')")
    or warn("Error creating $group2Name");

  insert("INSERT INTO groupmembers VALUES ($group2InstanceId,'$doctor3Id','')")
    or warn("Error adding $doctor3Email to $group2Name Group");

  insert("INSERT INTO groupadmins VALUES ($group2InstanceId,'$doctor3Id','')")
    or warn("Error adding $doctor3Email as admin for $group2Name Group");

  insert("INSERT INTO practice VALUES ($practice2Id,'$group2Name Practice',$group2InstanceId,
         '".$GLOBALS['Accounts_Url']."ws/R.php?pid=$practice2Id','http://www.rbh.org.uk/images/why_choose/local_hospital.jpg','$groupAcctId')")
    or warn("Error creating $group2Name Practice");

  insert("INSERT INTO todir (id,td_owner_accid,td_xid,td_alias,td_contact_list,td_shared_group,td_pin_state,td_contact_accid) 
          VALUES (65,'$doctor3Id','','$doctor3Email','$doctor3Email',0,0,'$doctor3Id')")
    or warn("Error inserting $doctor3Email to todir");

  insert("INSERT INTO practiceccrevents (practiceid,PatientGivenName,PatientFamilyName,PatientIdentifier,PatientIdentifierSource,Guid,Purpose,SenderProviderId,ReceiverProviderId,DOB,CXPServerURL,CXPServerVendor,ViewerURL,Comment,CreationDateTime,ConfirmationCode,RegistrySecret,PatientSex,PatientAge,Status,ViewStatus)
          VALUES (20,'user','one','$user1Id','Patient MedCommons Id','1234567890123456789012345678901234567890','a test ccr','idp','idp','16 Jan 1968 05:00:00 GMT','','MedCommons','','\n            3D Imaging Consult\n            ',1162365858,'0123456789012','','Female','','New','Visible')")
    or warn("Error inserting user1 ccr to worklist");


  try {
    $rightsUrl = $GLOBALS['Commons_Url']."/testdata.php";
    echo "Calling $rightsUrl";
    $rightsResult = get_url($rightsUrl);
    if($rightsResult==false) {
      err("Unable to grant rights for $janesEmail to access current ccr $demoCcrGuid");
    }
  }
  catch(Exception $ex) {
    warn("Unable to create rights for test accounts:  $ex");
  }

  // Add a fax cover sheet 
  insert("insert into cover (cover_id, cover_account_id, cover_notification, cover_encrypted_pin, cover_provider_code,cover_title, cover_note)
                              values ($coverSheetId, '$user2Id','$user2Email', '".sha1("12345")."', '0', 'Test Document','A note about the Test Document')")
    or warn("Error creating fax cover sheet for $user2Email");

  echo "<div style='border: solid 1px #8a6; font-family: arial; font-size: 9px;'><ul>$insertBuffer</ul></div>";
?>
<p>Test Data Created!</p>
  <form action="?delete=true" method="post">
    <p><input type="submit" value="Delete Test Data Now"/></p>
  </form>
<?
}

if($render) {
?>
  <p>This page creates the standard MedCommons Test Data for your system.</p>
<?if($dataExists) {
  echo "<p><b>Note: an existing user $doctorId ($existingEmail) was found on this system</b></p>";
}?>
  <form action="?create=true" method="post">
    <p><input type="submit" value="Create Test Data Now"/></p>
  </form>
  <form action="?delete=true" method="post">
    <p><input type="submit" value="Delete Test Data Now"/></p>
  </form>
<?
}
?>
</body>
</html>
