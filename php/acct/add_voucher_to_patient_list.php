<?
/**
 * Verifies a submitted voucher id / password and if correct, 
 * adds the patient specified by the voucher to the current user's
 * patient list.
 */
require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "urls.inc.php";
require_once "JSON.php";

nocache();

$json = new Services_JSON();
$result = new stdClass();

try {
    $vcode = strtoupper(req('vcode'));
    if(preg_match("/^[A-Z]{4,10}$/",$vcode) !== 1)
	    throw new Exception("Invalid value for parameter 'vcode'");
       
	$password = req('voucher_password');
	if(!$password || ($password == ''))
	    throw new Exception("Invalid or blank value for password");
	    
	$voucher = pdo_first_row("select * from modcoupons where voucherid = ? and otp = ?", array($vcode, $password));
	if(!$voucher)
	    throw new Exception("Invalid password or voucher id.  Please try again.");
	
	$info = get_validated_account_info();
	if(!$info->practice)
	   throw new Exception("User who is not a group member:  you must be a member of a group to use this function.");
	
	$gwUrl = allocate_gateway($voucher->mcid);
	$gwResult = $json->decode(get_url($gwUrl."/AddToAccount.action?storageId="
					                        .urlencode($voucher->mcid)
					                        ."&auth=".$info->auth
					                        ."&accessAuth=".$voucher->auth
					                 )
					         );
    if($gwResult->status != "ok")
        throw new Exception("Failure calling gateway to add account to patient list: ".$gwResult->error);
    
    dbg("Successfully added patient to patient list");
    
    $result->status = "ok";
}
catch(Exception $e) {
    $result->status = "failed";
    $result->error = $e->getMessage();
}
echo $json->encode($result);
?>
