<?php
$GLOBALS['newfeatures']=true;
$GLOBALS['DB_Connection'] = "mysql.internal";

$GLOBALS['DB_Database'] = "hbtest";
$GLOBALS['DB_Password'] = "purple44";  // ssadedin - note this password is wrong
$GLOBALS['DB_User']= "medcommons";

$GLOBALS['facebook_application_url']='http://apps.facebook.com/healthbooktest';
$GLOBALS['healthbook_application_name']='MedCommons HealthBookTest';
$GLOBALS['healthbook_application_version']='Beta 0.3.2';
$GLOBALS['healthbook_application_image']='http://photos-000.ll.facebook.com/photos-ll-sctm/v184/74/3/14458255649/s14458255649_2114463_5.jpg';
$GLOBALS['healthbook_application_publisher'] = 'MedCommons, Inc.';
$GLOBALS['facebook_config']['debug']=false;//donner
$GLBOALS['facebook_userid']=773104953;// this is adrian
$GLOBALS['appliance_key'] = 'f086ef8b5c141a6d1768157b6617e7fde25e07fb';
$GLOBALS['appliance_app_code'] = 'HEALTHBOOKTEST';


$oauth_consumer_key = "bde1fcb33cd5853fa67fea6476a3dcc9932c3011";
$oauth_consumer_secret = "fa7259f41447ff8e9fda5c4328db0c40fdd4c687";

require_once 'facebook.php';

// TODO: Change the URL below to the callback URL for your app.
$appcallbackurl = 'http://healthurl.myhealthespace.com/hbtest/';  

// TODO: Fill in the API key for your app below.
$appapikey = '5d618cf13fd9c1f011bc6809d23e0546';

// TODO: Fill in the secret for your app below.
$appsecret = '5db09760ea77f36b5f3ad0398e3a0b0a';





?>
