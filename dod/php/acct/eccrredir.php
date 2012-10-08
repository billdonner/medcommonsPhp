<?header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
require_once "dbparamsidentity.inc.php";

  function err($msg) {
    header("Location: error.php?msg=".urlencode($msg));
    exit;
  }
  
  //$pin = $_REQUEST['pin']; // these seem to never be used herein
 // $pinHash = sha1($pin);
  $accid = $_REQUEST['accid'];

  mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password']) or err("Error connecting to database.");
  $db = $GLOBALS['DB_Database'];
  mysql_select_db($db) or err("can not connect to database $db");

  // Find tracking number
  $result = mysql_query("select tracking, guid from ccrlog where accid = $accid and status = 'RED'");
  if(!$result) {
    err("Unable to query ccrlog record ".mysql_error());
  }

  if(mysql_num_rows($result)>0) { // Found emergency CCR

    $row = mysql_fetch_array($result);
    $trackingNumber = $row[0];
    $guid = $row[1];

    // Check if the document is public or not
    $result = mysql_query("select dt_privacy_level from document_type where dt_account_id = '$accid' and dt_type = 'Emergency CCR'");
    if(!$result) {
      err("Unable to query document_type record ".mysql_error());
    }

    if(mysql_num_rows($result)>0) { // found eccr row
      $row = mysql_fetch_array($result);
      if($row[0] == "Public") {
        // Public - good - send them there without a PIN
        $curl =$GLOBALS['Commons_Url']."gwredirguid.php?guid=$guid&free";
        header("Location: $curl");
        exit;
      }
    }

    $curl =$GLOBALS['Commons_Url'].'gwredir.php' ;
    header("Location: $curl?tracking=$trackingNumber");
  }

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <style type="text/css" media="all"> 
      @import "acctstyle.css"; 
      * {
        font-size: 12px;
      }
    </style>
  </head>
  <body style='background: transparent;'>
  <h3>No Emergency CCR Found</h3>
  <p>No Emergency CCR could be found for the Account you specified.</p>
  </body>
</html>

