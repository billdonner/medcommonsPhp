<?PHP

$GLOBALS['DB_Connection'] = "mysql.internal"; 
$GLOBALS['DB_User']= "web_ro";
$GLOBALS['DB_Database'] = "mcidentity";

include("urls.inc.php");

// Over ride values with those from local file
if(file_exists("dbparams.local.inc.php")) {
  include("dbparams.local.inc.php");
}

if(file_exists("../dbparams.local.inc.php")) {
  include("../dbparams.local.inc.php");
}

if(file_exists("../../dbparams.local.inc.php")) {
  include("../../dbparams.local.inc.php");
}

?>
