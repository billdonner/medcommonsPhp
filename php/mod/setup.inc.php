<?php

require_once "settings.php";
require_once "urls.inc.php";

global $Secure_Url;

$GLOBALS['html_deploy_location']='/var/www/html/htm/';

$GLOBALS['mod_base_url']="$Secure_Url/mod";
$GLOBALS['appliance'] = "$Secure_Url/";
$GLOBALS['appliance_accts'] = "$Secure_Url/acct/";
$GLOBALS['activate_accounts'] = false;
$GLOBALS['appliance_gw'] = "$Secure_Url/router/";
$GLOBALS['voucherid_solo']=false;
$GLOBALS['mod_website']="$Secure_Url";
$GLOBALS['voucher_pickupurl']="$Secure_Url/pickuprecords.php";

if(file_exists("local_mod_config.php")) {
  include "local_mod_config.php";
}

// these might get tweaked if debugging with amazon
$GLOBALS['amazon_pipeline'] = 'https://authorize.payments.amazon.com/pba/paypipeline';
$GLOBALS['devpay_prod_url'] = "https://aws-portal.amazon.com/gp/aws/user/subscription/index.html?offeringCode=68A808C7";

//there is only one return point from amazon devpay per product, we change this to someplace else in globals
$GLOBALS['devpay_redir'] = $acAmazonRedirectorUrl;

// these all represent full urls that are passed to external services, etc
// they might all be eliminated and just computed where needed

$GLOBALS['mod_base_url']=$GLOBALS['appliance']."/mod";
$GLOBALS['cc_purchase_done'] = $GLOBALS['mod_base_url']."/voucherhome.php";
$GLOBALS['fps_ipn'] = $GLOBALS['mod_base_url']."/voucherfpsipn.php";
$GLOBALS['fps_purchase_done'] = $GLOBALS['mod_base_url']."/catalog.php";
$GLOBALS['fps_voucherpurchase_done'] = $GLOBALS['mod_base_url']."/voucherpaidfps.php";
$GLOBALS['fps_return']=$GLOBALS['mod_base_url']."/voucherfpsreturn.php";
$GLOBALS['fps_abandon']= $GLOBALS['mod_base_url']."/voucherfpsabandon.php";
$GLOBALS['remote_wscounters_service'] = $GLOBALS['mod_base_url']."pay/wsCounters.php";
$GLOBALS['appliance_accts'] = $GLOBALS['appliance']."acct/";

// ssadedin: the gw is not always the one on the appliance itself
// allow for local config to modify that, only set it if unset
if(!isset($GLOBALS['appliance_gw']))
  $GLOBALS['appliance_gw'] = $GLOBALS['appliance'].'/router/';

if(!isset($GLOBALS['voucher_pickuproiurl']))
  $GLOBALS['voucher_pickuproiurl']=rtrim($GLOBALS['mod_website'],'/').'/pickuproireq.php';



// ssadedin: MOD settings inherited from settings
// customize by modifying local_settings.php
global $MOD_HOST, $MOD_DB, $MOD_USER;
$GLOBALS['DB_Connection'] = $MOD_HOST;
$GLOBALS['DB_Database'] = $MOD_DB;
$GLOBALS['DB_User']= $MOD_USER;

if(!isset($GLOBALS['appliance_access_token'])) {
}

if(!isset($GLOBALS['appliance_access_secret'])) {
}

?>
