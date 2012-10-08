<?PHP
$GLOBALS['DB_Connection'] = "mysql.internal"; 
$GLOBALS['DB_User']= "medcommons";
$GLOBALS['DB_Database'] = "mcextio";

include("urls.inc.php");

$GLOBALS['Tracking_Url'] = $Secure_Url . "/secure/trackemail.php";

?>
