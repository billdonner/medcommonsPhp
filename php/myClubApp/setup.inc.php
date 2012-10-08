<?php
// setup.inc.php - constants and customizations for medcommons sample myClub app

// change these depending upon which appliance is actually making healthurls - in particular, you will need to change this for a VMWare Appliance
$GLOBALS['appliance'] = 'https://healthurl.myhealthespace.com/'; // where urls get made
$GLOBALS['appliance_access_token'] ='98120771e3753e7b2160c7757b95b75787b710ef ';
$GLOBALS['appliance_access_secret'] ='8e597934ee6c1dca2aca15dd4573e1855ed5b15c';

// change these as required by your database 
$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_User']= "medcommons";
$GLOBALS['DB_Database'] = "myclub00";

// change these as required  by your application or website
$GLOBALS['member_start_page'] = 'index.php?err=OK+YouAreSIgnedOnAs+Member';
$GLOBALS['officer_start_page'] = 'index.php?err=OK+YouAreSIgnedOnAs+Officerr';
?>