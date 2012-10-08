<?header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
require_once "dbparamsidentity.inc.php";

  function err($msg) {
    header("Location: error.php?msg=".urlencode($msg));
    exit;
  }
  
  $pin = $_REQUEST['pin'];
  $pinHash = sha1($pin);
  $errmsg = "";
  $mc = $_COOKIE['mc'];
  if(($mc == null) || ($mc == ""))
    err("Must be logged in to perform this function");

  // Find account id
  preg_match("/mcid=([0-9]{16})/",$mc, $mcids);
  if(count($mcids)<2) {
    err("Bad cookie format in cookie $mc.\n\n matches=".count($mcids));
  }
  $accid = $mcids[1];  

  mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password']) or err("Error connecting to database.");
  $db = $GLOBALS['DB_Database'];
  mysql_select_db($db) or err("can not connect to database $db");

  $query = "select tracking from ccrlog where accid = $accid and status = 'RED'";
  $result = mysql_query ($query) or err("Error executing query on ccrlog - ".mysql_error());
  $rowcount = mysql_num_rows($result);
  if($rowcount == 0) {
    err("No Emergency CCR found for account $accid");
  }
  
  $row = mysql_fetch_array($result);
  $trackingNumber = $row[0];

  // Figure out the eccr 
  // Check pin is valid for emergency ccr
  $valid = file_get_contents($GLOBALS['Commons_Url']."ws/validateDocument.php?trackingNumber=$trackingNumber&pinHash=$pinHash");
  if(preg_match("/<summary_status>not found<\\/summary_status>/",$valid)) {
    header("Location: clearPin.php?tryagain=1");
    exit;
  }

  // If already there, delete
  $result = mysql_query("delete from document_type where dt_account_id = $accid and dt_type = 'Emergency CCR'");

  // Add eccr record 
  $result = mysql_query("insert into document_type (dt_id, dt_account_id, dt_type, dt_tracking_number, dt_privacy_level) 
    values (NULL, '$accid', 'Emergency CCR', '$trackingNumber', 'Public' )");
  if(!$result) {
    err("Unable to insert document_type record ".mysql_error());
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
  <h3>PIN Cleared</h3>
  <p>Your PIN has heen cleared from your Emergency CCR.  This CCR is now accessible via direct
  Account ID access from <a href="http://www.medcommons.net">www.medcommons.net</a></p>
  <p><a href="myccrlog.php?accid=<?echo $accid?>">Return to Account Page</a>.
  </body>
</html>

