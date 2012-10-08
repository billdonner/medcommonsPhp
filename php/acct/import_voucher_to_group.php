<?

/**
 * Imports a specified page to the current logged in user's group and forwards them 
 * to the dashboard, if they are logged in.
 */

require_once "utils.inc.php";
require_once "alib.inc.php";


$vcode = strtoupper(req('voucherid'));

if(preg_match("/^[A-Z]{4,10}$/",$vcode) !== 1)
    throw new Exception("Invalid value for parameter 'voucherid'");
 
$addUrl = "import_voucher_to_group.php?voucherid=".urlencode($vcode);

$info = get_validated_account_info();
if(!$info) {
    // Not logged in - we must make them log in and then forward 
    // back to here to try again
    header("Location: login.php?next=".urlencode($addUrl));
    exit;
}

if(!$info->practice)  {
    // TODO:  this must be handled better
    throw new Exception("You must be a member of a group to use this function.");
}
    
// They are logged in and a member of a group - we can 
// prompt for password
header("Location:  home.php?voucherid=".urlencode($vcode));

?>
