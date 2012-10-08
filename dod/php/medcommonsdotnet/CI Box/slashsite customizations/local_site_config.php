<?php

/*

this is for the ci development machine

*/

$WEBSITE = 'ci.myhealthespace.com';  // no not include http or s
$GLOBALREDIRECTOR = 'https://ci.myhealthespace.com'; // should always go to s
$GLOBALS['global_login_url']=$GLOBALREDIRECTOR.'/login/';
$GLOBALS['purchase_disabled'] = true;

$SOLOHOST='ci.myhealthespace.com'; //only important if running single appliance configuration 

function select_random_appliance() {
	// Hack for simon's sake
	// if(strpos($_SERVER['SCRIPT_URI'],"mc:7080") !== false) {
	// return "http://mc:7080";
	// }
	//return "https://".'s000'.rand(0,1).".myhealthespace.com";

	return "https://ci.myhealthespace.com"; //where to allocate urls
}


?>
