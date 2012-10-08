<?php
$GLOBALS['newfeatures']=true;
$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_User']= "root";
$GLOBALS['DB_Database'] = "healthbooktestrig";
$GLOBALS['DB_Password'] = "purple44"; 
$GLOBALS['facebook_application_url']='http://apps.facebook.com/hbtestrig';
$GLOBALS['healthbook_application_name']='UnCommon HealthBook';
$GLOBALS['healthbook_application_version']='Beta 0.3.1';
$GLOBALS['healthbook_application_image']='http://photos-000.ll.facebook.com/photos-ll-sctm/v184/74/3/14458255649/s14458255649_2114530_5960.jpg';
$GLOBALS['healthbook_application_publisher'] = 'MedCommons, Inc.';
$GLOBALS['facebook_config']['debug']=false;//donner
$GLOBALS['appliance_key'] = 'f086ef8b5c141a6d1768157b6617e7fde25e07fb';
$GLOBALS['appliance_app_code'] = 'HEALTHBOOKTESTRIG';


require_once 'facebook.php';

// TODO: Change the URL below to the callback URL for your app.
$appcallbackurl = 'http://healthurl.myhealthespace.com/hbtestrig/';  

require_once 'facebook.php';

// TODO: Fill in the API key for your app below.
$appapikey = 'caa54b6b3b98e63fa5e7382d187d65aa';

// TODO: Fill in the secret for your app below.
$appsecret = '8d486c96bf507ec0d65b99024ee83c03';

?>