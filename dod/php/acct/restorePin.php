<?header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  require_once "dbparamsidentity.inc.php";
  require_once "alib.inc.php";

  function err($msg) {
    error_page($msg);
    exit;
  }
  
  $pin = $_REQUEST['pin'];
  $pinHash = sha1($pin);
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

  // Blank PIN
  $result = mysql_query("update document_type set dt_privacy_level = 'Private' where dt_account_id = $accid and dt_type = 'Emergency CCR'");
  if(!$result) {
    err("Unable to update document_type record ".mysql_error());
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
  <h3>PIN Restored</h3>
  <p>The PIN for your Emergency CCR has heen restored.  Access to this CCR now requires entry of the CCR PIN.</p>
  <p><a href="goStart.php">Return to Account Page</a>.</p>
  </body>
</html>

