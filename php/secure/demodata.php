<?
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

require_once "settings.php";
require_once "urls.inc.php";

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

$janesId =   "1013062431111407";
$groupAcctId = "1172619833385984";
$demoDoctorAcctId = "1117658438174637";


$janesId =   "1013062431111407";
$janesEmail =  "jhernandez@medcommons.net";
$jane = new Patient($janesId, $janesEmail, "Jane", "Hernandez");
$demoCcrGuid = "a42df2f5bc06ec60017bdd1658ecc0535144b146";
$demoCcrGuid2 = "37e1d54bed6c50934e347d12a1ac30c4384f7da5";
$demoGroupAuth = '9e9596cc8117d36327370a219e5bb7692ca23137';
$demoDoctorAuth = '4e9396cc8817d36327370a219e5bb7692ca26131';

$jimsId =   "1106558614028065";
$jimsEmail =   "jjones@medcommons.net";
$jim = new Patient($jimsId, $jimsEmail, "Jim", "Jones");

$jbewell = new Patient("1088448116240388", "jbewell@medcommons.net", "Jane", "Bewell", "2715e40210467ef092c81614a401cbcfa4af026a");
$staylor = new Patient("1012576340589251","staylor@medcommons.net" , "Susan", "Taylor");

$patients = array($jane, $jim, $jbewell, $staylor);

$patientIds = array();
foreach($patients as $p) {
  $patientIds[]=$p->id;
}

$allPatients = implode(",",$patientIds);

$guids = array();
$guids[] = $demoCcrGuid;
$guids[] = $demoCcrGuid2;


global $CENTRAL_HOST,$CENTRAL_DB,$CENTRAL_USER,$CENTRAL_PASS;

mysql_connect($CENTRAL_HOST, $CENTRAL_USER, $CENTRAL_PASS) or err("Error connecting to database.");
mysql_select_db($CENTRAL_DB) or err("can not connect to database $db");

foreach($guids as $guid) {

  $result = mysql_query("select id from document where guid = '$guid'")
    or err("Unable to query document table");

  $row = mysql_fetch_array($result);
  if($row) {
    $docId = $row[0];
    if($docId && ($docId != '')) {
      mysql_query("delete from rights where account_id = $janesId and document_id = $docId")
          or err("Unable to delete old rights for account $janesId and document $docId .  Did you import the demo data yet?");

      // Give Jane rights to her Current CCR
      // Only necessary because the default import creates it under the doctors account, currently
      insert("INSERT INTO rights (rights_id,account_id,document_id,rights,creation_time,expiration_time,rights_time,storage_account_id) 
              VALUES (NULL,'$janesId',$docId,'RW',CURRENT_TIMESTAMP,NULL,CURRENT_TIMESTAMP,null)")
        or err("Unable to insert rights for user $janesId"); 
    }
  }
}

$consumerToken = '270ebdf10b9bb9dd957a4a14833367183a196da7';
$accessToken = '970efdf18b9bb9dd957a4a14833367283a116d37';

// Delete old auth tokens
mysql_query("delete r.* 
				from rights r, external_share es, authentication_token at
				where r.es_id = es.es_id
				and   at.at_es_id = es.es_id
				and   at.at_token = '$accessToken'");
				


mysql_query("delete es.* 
				from external_share es, authentication_token at
				where at.at_es_id = es.es_id
				and   at.at_token = '$accessToken'");

mysql_query("delete from authentication_token where at_token = '$accessToken'");
mysql_query("delete from authentication_token where at_token = '$consumerToken'");


// Add an external application
insert("REPLACE INTO external_application VALUES (NULL,'$consumerToken','DEMO','Demonstration Application','Pending',NULL,'2009-02-14 04:28:08','72c25b142d4dd453213b586fc1278afc446b1d89','http://www.medcommons.net','ssadedin@medcommons.net')")
    or err("Error adding demo external application");
   
mysql_query("INSERT INTO authentication_token (at_id, at_token, at_secret) VALUES (NULL, '$consumerToken', '72c25b142d4dd453213b586fc1278afc446b1d89')")
    or err("Error adding demo external application token");    
    
$atId = mysql_insert_id();

// Add external application rights to Jane
insert("INSERT INTO external_share (es_id , es_identity , es_identity_type) values (NULL, 'Demonstration Application', 'Application')")
    or err("Error adding demo external application share");

$esId = mysql_insert_id();

// Add access token for external app
mysql_query("INSERT INTO authentication_token (at_id, at_token, at_secret, at_es_id, at_parent_at_id) VALUES (NULL, '$accessToken', '32c25b142d4dd453213b586fc1278afc446b1d49', $esId, $atId)")
    or err("Error adding demo external application token");
    
  
// Give Demo Doctor Group rights to patient accounts
foreach($patients as $p) {
    mysql_query("delete from rights where account_id = '$groupAcctId' and storage_account_id = '".$p->id."'")
      or err("Unable to delete old rights for groupAcctId=$groupAcctId and storage account ".$p->id);
      
    insert("INSERT INTO rights (rights_id,account_id,document_id,rights,creation_time,expiration_time,rights_time,storage_account_id) 
            VALUES (NULL,'$groupAcctId',NULL,'RW',CURRENT_TIMESTAMP,NULL,CURRENT_TIMESTAMP,'".$p->id."')")
      or err("Unable to insert rights for demo doctor group to access account ".$p->id."/".$p->email); 
      
    insert("INSERT INTO rights (rights_id,rights,es_id, storage_account_id) 
            VALUES (NULL,'RW',$esId,'".$p->id."')")
      or err("Unable to insert rights for external application to access account ".$p->id."/".$p->email); 
 }

// Create an auth token to login as demodoctor
mysql_query("delete from authentication_token where at_token in ('$demoGroupAuth', '$demoDoctorAuth')");
insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time,at_priority) 
        VALUES (NULL,'$demoGroupAuth','$groupAcctId', CURRENT_TIMESTAMP, 'G')")
  or err("Unable to add authentication token for demo group");

insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time,at_priority) 
        VALUES (NULL,'$demoDoctorAuth','$demoDoctorAcctId', CURRENT_TIMESTAMP, 'I')")
  or err("Unable to add authentication token for demo doctor");
insert("INSERT INTO authentication_token (at_id,at_token,at_account_id,at_create_date_time,at_priority) 
        VALUES (NULL,'$demoDoctorAuth','$groupAcctId', CURRENT_TIMESTAMP, 'G')")
  or err("Unable to add authentication token for demo doctor");
  
  


?>
Demo Rights for users <?=$allPatients?> inserted.
<br/>
<?echo $insertBuffer;?>
