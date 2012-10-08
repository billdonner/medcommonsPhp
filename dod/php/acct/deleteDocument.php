<?header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  require_once "dbparamsidentity.inc.php";
  require_once "ccrloglib.inc.php";
  require_once "utils.inc.php";
  require_once "alib.inc.php";
  
  $dtId = $_REQUEST['dtId'];
  $accid = get_validated_account_info()->accid;
  initdb();

  $result = mysql_query ("select dt_type from document_type where dt_id = $dtId and dt_account_id = $accid") or err("Error deleting account document $dtId");
  $dtType = mysql_fetch_row($result);
  $dtType = $dtType[0];
  if( (!$dtType) || ($dtType == ''))
    err("Unknown document type for document $dtId");

  error_log("dtType = $dtType");
  $update = "update document_type set dt_privacy_level = 'Deleted' where dt_account_id = $accid and dt_type = '$dtType'";
  error_log($update);
  $result = mysql_query ($update) or err("Error deleting account document $dtId");
  
  // Forward to the ccr log page
  header("Location: goStart.php?msg=".urlencode("Your document has been deleted."));
?>
