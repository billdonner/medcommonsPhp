<?PHP
$GLOBALS['DB_Connection'] = "mysql.internal"; 
$GLOBALS['DB_User']= "web_ro";
$GLOBALS['DB_Database'] = "mcextio";

include("urls.inc.php");

$GLOBALS['Tracking_Url'] = $Secure_Url . "/secure/trackemail.php";

// Over ride values with those from local file
if(file_exists("dbparams.local.inc.php")) {
  include("dbparams.local.inc.php");
}



?>
