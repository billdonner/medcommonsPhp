<?php

/*

this is for the s0010  development machine

*/

$WEBSITE = 's0010.myhealthespace.com';  // no not include http or s
$GLOBALREDIRECTOR = 'https://s0010.myhealthespace.com'; // should always go to s
$GLOBALS['global_login_url']=$GLOBALREDIRECTOR.'/login/';
$GLOBALS['purchase_disabled'] = true;

$SOLOHOST='s0010.myhealthespace.com'; //only important if running single appliance configuration 

function select_random_appliance() {
	// Hack for simon's sake
	// if(strpos($_SERVER['SCRIPT_URI'],"mc:7080") !== false) {
	// return "http://mc:7080";
	// }
	//return "https://".'s000'.rand(0,1).".myhealthespace.com";

	return "https://s0010.myhealthespace.com"; //where to allocate urls
}


?>
