<?PHP
//
// to install your own medcommons central you must, at the very least, set a proper value for both $Homepage_Url
//   and $Secure_Url - they must both be distinct places, and $Secure_Url will preferrably be an SSL/https: site
//

$Domain = $_SERVER['SERVER_NAME'];
$Homepage_Url = "http://" . $Domain;
$Secure_Url = "https://" . $Domain;

// at the limit, you can break this distro into seven separate services:
//
// the Website  ($Homepage_Url)
// the Document Service ($Secure_Url)
// the Accounts Service (Accounts_Url,Groups_Url,Extensions_Url)
// the Notification Service (set in the gateway)
// the Payments Service (Payments_Url)
// the Identity Service (Identity_Base_Url)
// the Trackers Service (Trackers_Url) can also  be set up as a third party app
//

$GLOBALS['DB_Password']="";  // you may need to adjust this is the various dbparams*.inc.php files
$GLOBALS['Homepage_Url']= $Homepage_Url;

$GLOBALS['layout_tpl_php'] ='layout.tpl.php';
$GLOBALS['show_ads'] =true;
$GLOBALS['resident_editors']=true; 
$GLOBALS['registration_disabled']=false; 
$GLOBALS['VxFarm_Url']=$Homepage_Url.'/vxfarm/';  

$GLOBALS['Identity_Base_Url'] = $Secure_Url . "/identity/";
$GLOBALS['Payments_Url'] = $Secure_Url . "/pay/";
$GLOBALS['Groups_Url'] = $Secure_Url . "/groups/";
//despite these fancy logical urls, there are codepencies - appsrv and acct must be on same IP
$GLOBALS['Extensions_Url'] = $Secure_Url . "/acct/";
$GLOBALS['Trackers_Url'] = $Secure_Url . "/trackers/";

$GLOBALS['Accounts_Url'] = $Secure_Url . "/acct/";
$GLOBALS['Commons_Url'] = $Secure_Url . "/secure/";
$GLOBALS['Script_Domain'] = $Domain;
$GLOBALS['SecureLoginUrl'] = $Secure_Url . "/acct/goStart.php";
$GLOBALS['BASE_WWW_URL'] = $Homepage_Url . "/";
$GLOBALS['use_combined_files']=true;
$GLOBALS['Acct_Combined_File_Base']=$GLOBALS['Accounts_Url'];  

$GLOBALS['Default_Repository'] = $Secure_Url . "/router";

// Where urls to the web site should point
// this is kind of ambiguous, but in svn it lives
// under /site, on some machines it's deployed to /
// Customization here lets us change it to run out of svn 
// which is very nice for development
$GLOBALS['Site_Url'] = $Secure_Url;
?>
