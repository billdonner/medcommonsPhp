<?PHP

$Homepage_Url = "http://beta.medcommons.org";
$Secure_Url = "https://beta.medcommons.org";
$GLOBALS['DB_Password']="";
$GLOBALS['Homepage_Url']= $Homepage_Url;

$GLOBALS['Identity_Base_Url'] = $Secure_Url . "/identity/";
$GLOBALS['Payments_Url'] = $Secure_Url . "/pay/";
$GLOBALS['Groups_Url'] = $Secure_Url . "/groups/";
//despite these fancy logical urls, there are codepencies - appsrv and acct must be on same IP
$GLOBALS['Extensions_Url'] = $Secure_Url . "/acct/";
$GLOBALS['Trackers_Url'] = $Secure_Url . "/trackers/";

$GLOBALS['Accounts_Url'] = $Secure_Url . "/acct/";
$GLOBALS['Commons_Url'] = $Secure_Url . "/secure/";
$GLOBALS['Script_Domain'] = "medcommons.net";

$GLOBALS['SecureLoginUrl'] = $Secure_Url . "/acct/goStart.php";


// Sadly it seems our deployments are non-uniform and this it's impossible to 
// reference everything by relative path.  Hence in some places absolute url's
// must be generated (ugh)
$GLOBALS['BASE_WWW_URL'] = $Homepage_Url . "/";


?>
