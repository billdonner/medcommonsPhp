<?php

require_once "settings.php";
require_once "urls.inc.php";

global $Secure_Url;
global $acDomain;
global $acGlobalsRoot;

// this is for the sxxx integration string
$GLOBALS['purchase_disabled'] = true;
$WEBSITE = $acDomain;  // no not include http or s
$WEBSITE_PROTOCOL = 'https';  
$GLOBALREDIRECTOR = $acGlobalsRoot;
$GLOBALS['global_login_url']=$GLOBALREDIRECTOR.'/login/';
$HAS_LOCAL_APPLIANCE=true;

$SOLOPROTOCOL='https';
$SOLOHOST=$acDomain; // only important if running single appliance configuration 
$CLUSTER_PREFIX='s';

if(file_exists("local_site_config.php")) {
  include "local_site_config.php";
}

if(!function_exists("select_random_appliance")) {
  function select_random_appliance() {
    global $Secure_Url;
    return $Secure_Url;
  }
}
?>
