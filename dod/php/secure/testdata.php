<?
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
require_once "urls.inc.php";
require_once "dbparams.inc.php";

$insertBuffer="";

function insert($sql) {
  global $insertBuffer;
  $insertBuffer.="<li>$sql</li>";
  return mysql_query($sql);
}
function err($msg) {
  $msg.="<br/><br/>".mysql_error();
  header("Location: error.php?msg=".urlencode($msg));
  exit;
}

class Patient {
  function Patient($id, $email, $first, $last,$currentCcr=null) {
    $this->id = $id;
    $this->email = $email;
    $this->first = $first;
    $this->last = $last;
    $this->ccr = $currentCcr;
  }
}

$doctorId = "1259366818364933";
$doctorEmail =  "doctor@medcommons.net";
$doctor2Email = "doctor2@medcommons.net";
$doctor3Email = "doctor3@medcommons.net";

$user1Email = "user1@medcommons.net";
$user2Email = "user2@medcommons.net";

$practiceId = 20;
$groupInstanceId = 32;
$groupAcctId="1175376381039160";

$doctor2Id = "1166439538173659";
$doctor3Id = "1035582511657478";

$user1Id = "1162164444007929";
$user2Id = "1087997704966332";

$user1Auth = "97a49ea6137dc95bc02a3775282b5e19c47d7892";
$doctor3Auth = "77d49ea6137ec95bc02a3475282b5a1bc47d7872";

$group2InstanceId = 33;
$group2AcctId="1012576340589251";
$group2Name="Better Doctors";


mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password']) or err("Error connecting to database.");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or err("can not connect to database $db");

mysql_query("delete es.*, r.* from external_share es, rights r 
  where r.es_id = es.es_id and r.storage_account_id = '$user2Id'")
  or err("Unable to delete old rights.");


// Delete all rights for user1
mysql_query("delete from rights where account_id = '$user1Id'")
  or err("Unable to delete old rights.");

// Give user1 rights to user2's account 
insert("INSERT INTO rights (rights_id,account_id,document_id,rights,creation_time,expiration_time,rights_time,storage_account_id) 
        VALUES (NULL,'$user1Id',NULL,'RW',CURRENT_TIMESTAMP,NULL,CURRENT_TIMESTAMP,'$user2Id')")
  or err("Unable to insert rights for user1 to access user2 account"); 

// Create an auth token to login as user1
mysql_query("delete from authentication_token where at_token = '97a49ea6137dc95bc02a3775282b5e19c47d7892'");
insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time) 
        VALUES (NULL,'$user1Auth','$user1Id', CURRENT_TIMESTAMP)")
  or err("Unable to add authentication token for user1");

// Create an auth token to login as user2
mysql_query("delete from authentication_token where at_token = '87a49ea6137dc95bc02a3775282b5e19c47d7893'");
insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time) 
        VALUES (NULL,'87a49ea6137dc95bc02a3775282b5e19c47d7893','$user2Id', CURRENT_TIMESTAMP)")
  or err("Unable to add authentication token for user2");

// Create an auth token for doctorId
mysql_query("delete from authentication_token where at_token = 'd5d813d968b8ae64088b37be1d1ff82addfbab41'");
insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time) 
        VALUES (NULL,'d5d813d968b8ae64088b37be1d1ff82addfbab41','$doctorId', CURRENT_TIMESTAMP)")
  or err("Unable to add authentication token for doctor");

insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time) 
        VALUES (NULL,'d5d813d968b8ae64088b37be1d1ff82addfbab41','$groupAcctId', CURRENT_TIMESTAMP)")
  or err("Unable to add authentication token for doctor");

insert("INSERT INTO external_share (es_id,es_identity,es_identity_type,es_first_name,es_last_name) VALUES (NULL,'http://foo.openid.test.medcommons.net/','openid',null,null)")
  or err("Unable to add authentication token for openid");

$esId = mysql_insert_id();
insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time,at_es_id,at_secret,at_parent_at_id) 
            VALUES (NULL,'97a49ea6137dc94bc02a3775282b5e19c47d7898','',CURRENT_TIMESTAMP,$esId,null,null)");

// Create an auth token for doctor3
mysql_query("delete from authentication_token where at_token = '$doctor3Auth'");
insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time) 
        VALUES (NULL,'$doctor3Auth','$doctor3Id', CURRENT_TIMESTAMP)")
  or err("Unable to add authentication token for doctor");

insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time) 
        VALUES (NULL,'$doctor3Auth','$group2AcctId', CURRENT_TIMESTAMP)")
  or err("Unable to add authentication token for doctor3");

?>
  <html><body><p>Test data inserted successfully!</p><?=$insertBuffer?></body></html>
