<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
require_once "../alib.inc.php";
require_once "wslibdb.inc.php";
require_once "utils.inc.php";
require_once "login.inc.php";
require_once "mc.inc.php";

/**
 * Adds the specified voucher to the specified account's patient list
 *
 * @param accid - account id to add to patient list for
 * @param couponum  - coupon number to add
 */
class shareVoucherWs extends jsonrestws {
	function jsonbody() {

    $accid = clean_mcid(req('accid'));
    if(!is_valid_mcid($accid))
      throw new Exception("Invalid account id $accid");

    $couponum = req('couponum');
    if(preg_match("/^[0-9]{1,12}$/",$couponum)!==1) 
      throw new Exception("Invalid couponum $couponum");

    pdo_execute("insert into modcoupon_share (accid,couponum) values (?,?)", array($accid,$couponum));
    return "ok";
  }
}

$x = new shareVoucherWs();
$x->handlews("response_shareVoucher");
?>
