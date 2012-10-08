<?php

require 'healthbook.inc.php';
require_once "home.inc.php";
// about tab is in the library because it is directly invoked by main tab

//**start here
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->get_loggedin_user(); //require_login();


	connect_db();
	list($mcid,$appliance) = fmcid($user);
	

	// jumping off to outer space
	echo home($user,$mcid,FALSE,$facebook,$appliance,'care team' );

?>