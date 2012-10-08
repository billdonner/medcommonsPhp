<?php

$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_User']= "root";
$GLOBALS['DB_Database'] = "healthbookbeta";
$GLOBALS['DB_Password'] = "purple44"; 
$GLOBALS['facebook_application_url']='http://apps.facebook.com/healthbookbeta';
$GLOBALS['healthbook_application_name']='MedCommons HealthBookBeta';
$GLOBALS['healthbook_application_version']='Beta 0.3.1';
$GLOBALS['healthbook_application_image']='http://photos-000.ll.facebook.com/photos-ll-sctm/v184/74/3/14458255649/s14458255649_2114462_4092.jpg';
$GLOBALS['healthbook_application_publisher'] = 'MedCommons, Inc.';
$GLOBALS['facebook_config']['debug']=false;//donner
$GLOBALS['appliance_key'] = 'f086ef8b5c141a6d1768157b6617e7fde25e07fb';
$GLOBALS['appliance_app_code'] = 'HEALTHBOOKBETA';

require_once 'facebook.php';

// TODO: Fill in the API key for your app below.
$appapikey = '8461130ed49f975ee40e5b52d94b9f33';

// TODO: Fill in the secret for your app below.
$appsecret = '68ac7ec09406c46f5fba0e79609d122e';

// TODO: Change the URL below to the callback URL for your app.
$appcallbackurl = 'http://healthurl.myhealthespace.com/hbbeta/';  

?>