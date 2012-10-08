<?header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  require_once "dbparamsidentity.inc.php";
  require_once "ccrloglib.inc.php";
  require_once "utils.inc.php";
  require_once "alib.inc.php";

  if (array_key_exists('documentType', $_REQUEST)) {
    $docType = $_REQUEST['documentType'];
  } else {
    $docType = 'CURRENTCCR';
  }

  $docComment = $_REQUEST['comment'];
  $guid = $_REQUEST['guid'];

  // If user logged in then we continue to their account afterwards
  $continueAcct = false;
  if($_REQUEST['accid']) {
    $accid = $_REQUEST['accid'];
  }
  else {
    $info = get_validated_account_info();
    $accid = $info->accid;
    $continueAcct = true;
  }

  initdb();

  $insert = "insert into document_type (dt_id, dt_account_id, dt_type, dt_guid, dt_privacy_level,dt_comment) values
            (NULL, '$accid','$docType','$guid', 'Private','$docComment');";
  $result = mysql_query ($insert) or error_page("Error inserting account document.");
  
  // Forward to the ccr log page
  if($continueAcct) {
    header("Location: goStart.php");
    exit;
  }
?>
OK
