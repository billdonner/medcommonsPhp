<?php
$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_User']= "medcommons";
$GLOBALS['DB_Database'] = "healthbook";
$GLOBALS['DB_Password'] = "";
$GLOBALS['facebook_application_url']='http://apps.facebook.com/medcommons';
$GLOBALS['healthbook_application_name']='MedCommons HealthBook';
$GLOBALS['healthbook_application_version']='Beta 0.2.5';
$GLOBALS['facebook_config']['debug']=false;//donner

require_once 'facebook.php';

// TODO: Fill in the API key for your app below.
$appapikey = 'bf3ff3ab17da66dc9073b5e0d698319f';

// TODO: Fill in the secret for your app below.
$appsecret = 'feff2e5f2f69c33a9a72fd6c6cf91788';

$facebook = new Facebook($appapikey, $appsecret);
if (isset($GLOBALS['nologin'])) { $user = $facebook->get_loggedin_user();  echo "debug: no login required for this page - user $user<br/>";}// this is the only difference from appinclude.php
else $user = $facebook->require_login();

// TODO: Change the URL below to the callback URL for your app.
$appcallbackurl = 'http://healthbook.medcommons.net/';  
$facebook = new Facebook($api_key, $secret);
$facebook->require_frame();
$user = $facebook->require_login();
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