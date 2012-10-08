<?php
$GLOBALS['appliance'] = 'https://simtrak.medcommons.net/'; // where urls get made - the tokens are for health.medcommons
$GLOBALS['appliance_access_token'] = 'd0a900f98325ebaf62291371dc56baa2d94030d9';
$GLOBALS['appliance_access_secret'] = 'a7854167ce21d917ff3c4f5b16d843f1844f8c17';
$GLOBALS['mod_website']='https://www.medcommons.net';
$GLOBALS['activate_accounts'] = true;
$GLOBALS['voucherid_solo'] = true; // only one appliance in this config , they are not numbered
$GLOBALS['voucher_pickupurl']=$GLOBALS['mod_website'].'/pickuprecords.php'; // this should be back on our website
$GLOBALS['voucher_pickuproiurl']=$GLOBALS['mod_website'].'/pickuproireq.php';
?>
