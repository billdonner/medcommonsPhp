<?php


// this is for the sxxx integration string

$WEBSITE = 'www.myhealthespace.com';  // no not include http or s
$GLOBALREDIRECTOR = 'https://globals.myhealthespace.com'; // should always go to s
$GLOBALS['global_login_url']=$GLOBALREDIRECTOR.'/login/';
$GLOBALS['purchase_disabled'] = false;

function select_random_appliance() {
	// Hack for simon's sake
	// if(strpos($_SERVER['SCRIPT_URI'],"mc:7080") !== false) {
	// return "http://mc:7080";
	// }
	return "https://".'s000'.rand(0,1).".myhealthespace.com";

}




?>
