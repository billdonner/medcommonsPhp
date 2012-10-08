<?php

$GLOBALS['appliance'] = 'https://tenth.medcommons.net/'; // where urls get made - the tokens are for health.medcommons
$GLOBALS['appliance_access_token'] = '5488fb2b26d4d065058e77d1912f706ad2838a9a';//'////98120771e3753e7b2160c7757b95b75787b710ef';
$GLOBALS['appliance_access_secret'] ='fca3bb6f066726e4142feda34d33d3412e9ba3ce';//'8e597934ee6c1dca2aca15dd4573e1855ed5b15c';
$GLOBALS['mod_website']='https://www.medcommons.net';
$GLOBALS['activate_accounts'] = true;
$GLOBALS['voucherid_solo'] = true; // only one appliance in this config , they are not numbered
$GLOBALS['voucher_pickupurl']=$GLOBALS['mod_website'].'/pickuprecords.php'; // this should be back on our website
?>