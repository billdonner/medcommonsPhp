<?php
$GLOBALS['mod_website']="https://ci.myhealthespace.com/site/";
$GLOBALS['mod_base_url']="https://ci.myhealthespace.com/mod";
$GLOBALS['cc_purchase_done'] = $GLOBALS['mod_base_url']."/voucherhome.php";

// these settings must get redone on every MOD appliance - get the token and secret from the appliance console
// in this configuration, new healthURLs are made on this very appliance, whether created by vouchers or by user signup
$GLOBALS['activate_accounts'] = true;
$GLOBALS['voucherid_solo'] = false; // ss: kind of strange, but only when set to non-solo will it use the global record for claim url
$GLOBALS['voucher_pickupurl']=$GLOBALS['mod_base_url'].'/voucherclaim.php'; // this should be back on our website

// these all represent full urls that are passed to external services, etc
// they might all be eliminated and just computed where needed
$GLOBALS['appliance'] = 'https://ci.myhealthespace.com/'; // where urls get made - the tokens are for health.medcommons
$GLOBALS['cc_purchase_done'] = $GLOBALS['mod_base_url']."/voucherhome.php";
$GLOBALS['fps_ipn'] = $GLOBALS['mod_base_url']."/voucherfpsipn.php";
$GLOBALS['fps_purchase_done'] = $GLOBALS['mod_base_url']."/catalog.php";
$GLOBALS['fps_voucherpurchase_done'] = $GLOBALS['mod_base_url']."/voucherpaidfps.php";
$GLOBALS['fps_return']=$GLOBALS['mod_base_url']."/voucherfpsreturn.php";
$GLOBALS['fps_abandon']= $GLOBALS['mod_base_url']."/voucherfpsabandon.php";
$GLOBALS['remote_wscounters_service'] = $GLOBALS['mod_base_url']."pay/wsCounters.php";
$GLOBALS['appliance_accts'] = $GLOBALS['appliance']."acct/";
$GLOBALS['appliance_access_token'] ='ddf81fdf697b0d6be4dfc46c112f373210593ff5';
$GLOBALS['appliance_access_secret'] ='37b0d96a5041100cc8bfc90860b385be36d0578d';
$GLOBALS['activate_accounts'] = true;
$GLOBALS['appliance_gw'] = $GLOBALS['appliance'].'/router/';
$GLOBALS['html_deploy_location']='/var/www/html/htm/';

$GLOBALS['mod_appliance'] = "https://ci.myhealthespace.com/";

$GLOBALS['remote_wscounters_service'] = $GLOBALS['mod_base_url']."pay/wsCounters.php";

?>
