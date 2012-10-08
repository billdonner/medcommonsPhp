<?php
/**
 * Renders a HTML snippet containing details of a voucher.
 * <p>
 * Intended for display inside a div, dialog or panel.
 */
require_once "utils.inc.php";
require_once "mc.inc.php";
require_once "alib.inc.php";
require_once "urls.inc.php";
require_once "template.inc.php";

global $Secure_Url;

function lookup_vid($vcode) {
    
    if(preg_match("/^[A-Z]{4,10}$/",$vcode) !== 1)
	    throw new Exception("Invalid value for parameter 'vcode'");
    
    $v = pdo_first_row("select c.couponum, s.servicename
                        from modcoupons c, modservices s
                        where c.voucherid = ? and s.svcnum = c.svcnum",array($vcode));
    return $v ? $v->couponum : null;
}

nocache();
try {

    $t = req('t','popup');

    $vid = req('vid');
    if(!$vid || ($vid == "")) {
        dbg("No vid provided, trying vcode: ". req('vcode', 'null'));
        $vid = lookup_vid(req('vcode'));
    }
    
    if(!$vid) 
        throw new Exception("missing expected parameter 'vid'");

    if(preg_match("/^[0-9]*$/",$vid) !== 1)
        throw new Exception("Bad value $vid for parameter 'vid'");

    $v = pdo_first_row("select c.*, s.servicename
                        from modcoupons c, modservices s
                        where c.couponum = ? and s.svcnum = c.svcnum",array($vid));
    if(!$v)
      throw new Exception("Voucher $vid not found.");

    // We can get the use from two different places
    // a) the logged in user
    // b) a user passed in parameters
    if($accid = req('accid')) {
        if(!is_valid_mcid($accid,true))
		  throw new ValidationFailure("Invalid value for parameter 'accid'");
        
		$auth = req('auth');
		if(preg_match("/^[a-z0-9]{40}$/", $auth) !== 1)
		  throw new ValidationFailure("Invalid value for parameter 'auth'");
    }
    else {
	    $info = get_validated_account_info();
	    if(!$info)
	        throw new Exception("You must be logged in");
	
	    if(!$info->practice)
	        throw new Exception("You must be be a member of a group");
	    
	    $auth = $info->auth;
    }
	
	// Must have access to patient
    $consents = getPermissions($auth, $v->mcid); 
    if(strpos($consents,"W")===false) {
        echo template('prompt_voucher_password.tpl.php')
                ->set("v", $v)
                ->fetch();
        exit;        
    }

    if(isset($info) && $info->enable_dod) 
        $accessUrl = "http://dicomondemand.com";
    else
        $accessUrl = rtrim($Secure_Url,'/')."/mod/voucherclaim.php";
}
catch(Exception $e) {
    echo "<div><p>A problem occurred loading voucher details:</p>
          <pre>".htmlentities($e->getMessage())."</pre></div>";
    exit;
}
ob_start();
?>
    <table>
    <thead>
      <tr><th colspan='2'><h2><?=htmlentities($v->patientname)?></h2></th></tr>
    </thead>
      <tbody>
        <tr>
            <th>Voucher ID</th> <td><?=$v->voucherid?></td>
        </tr>
        <tr>
            <th>PIN</th> <td><?=$v->otp?></td>
        </tr>
        <tr>
            <th>Issued</th> <td><?=strftime("%x", $v->issuetime)?></td>
        </tr>
        <tr>
            <th>Expires</th> <td><?=strftime("%x", strtotime($v->expirationdate))?></td>
        </tr>
        <tr>
            <th>Access</th>
            <td><a title='URL used to claim this voucher by patient'
                   href='<?=$accessUrl?>'><?=$accessUrl?></a></td>
        </tr>
        <tfoot>
        <tr>
            <td>&nbsp;</td><td><button id='print' onclick='window.prt.location.href="displayVoucher.php?vid=<?=$vid?>&t=print";'>Print</button></td>
        </tr>
        </tfoot>
      </tbody>
    </table>
<?
$body = ob_get_contents();
ob_end_clean();
echo template("displayVoucher_$t.tpl.php")
        ->set("contents",$body)->fetch();
?>