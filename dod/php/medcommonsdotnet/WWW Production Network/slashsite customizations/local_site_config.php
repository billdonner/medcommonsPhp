<?php

/*

this is for the main web site touched 1136
hacked by bill to point $website back to the appliance itself

*/

$WEBSITE = 'tenth.medcommons.net';  // no not include http or s
$GLOBALREDIRECTOR = 'https://www.medcommons.net'; // should always go to s
$GLOBALS['global_login_url']=$GLOBALREDIRECTOR.'/login/';

// ssadedin: temporarily point it at itself until global login supports next
$GLOBALS['global_login_url']='https://tenth.medcommons.net/acct/login.php';

$GLOBALS['purchase_disabled'] = false;

$SOLOHOST='tenth.medcommons.net'; //only important if running single appliance configuration 

function select_random_appliance() {
	// Hack for simon's sake
	// if(strpos($_SERVER['SCRIPT_URI'],"mc:7080") !== false) {
	// return "http://mc:7080";
	// }
	//return "https://".'s000'.rand(0,1).".myhealthespace.com";

	return "https://tenth.medcommons.net"; //where to allocate urls
}


?>
