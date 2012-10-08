<?php
$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_User']= "medcommons";
$GLOBALS['DB_Database'] = "healthbook";
$GLOBALS['DB_Password'] = "";
$GLOBALS['facebook_application_url']='http://apps.facebook.com/medcommons';
$GLOBALS['healthbook_application_name']='HealthBook';
$GLOBALS['healthbook_application_version']='Beta 0.2.1';
$GLOBALS['facebook_config']['debug']=false;//donner
$GLOBALS['appliance_key'] = '600cf73bbb466a023cc54ed9100a11a6e21c7328';
$GLOBALS['appliance_app_code'] = 'HEALTHBOOK';

require_once 'facebook.php';

// TODO: Fill in the API key for your app below.
$appapikey = 'bf3ff3ab17da66dc9073b5e0d698319f';

// TODO: Fill in the secret for your app below.
$appsecret = 'feff2e5f2f69c33a9a72fd6c6cf91788';

$facebook = new Facebook($appapikey, $appsecret);
$user = $facebook->require_login();

// TODO: Change the URL below to the callback URL for your app.
$appcallbackurl = 'http://healthbook.medcommons.net/';  

$oauth_consumer_key = "f07b14cb9960a4690de5b5cf9d9a89bff232c489";
$oauth_consumer_secret = "aa9b158c2cf8f6d7542ae4a683f4df7a7f664a82";

//catch the exception that gets thrown if the cookie has an invalid session_key in it
try {
  if (!$facebook->api_client->users_isAppAdded()) {
    $facebook->redirect($facebook->get_add_url());
  }
} catch (Exception $ex) {
  //this will clear cookies for your application and redirect them to a login prompt
  $facebook->set_user(null, null);
  $facebook->redirect($appcallbackurl);
}
?>
