<?php

require_once "modpay.inc.php";
require_once 'login.inc.php';
require_once 'utils.inc.php';
require_once 'db.inc.php';

nocache();

$v = new stdClass;
$v->err = $v-> voucherid_err = $v->otp_err = $v->otp = $v->voucherid = '';

$header = page_header_nonav( "page_claim" ,"Claim Voucher - MedCommons on Demand");
$footer = page_footer();

$db = DB::get();

// error check these args
$errs = array (); $errstring ='';
if (isset($_REQUEST['voucheridsearch'])) {
  $v->voucherid = $_REQUEST['voucheridsearch'];
  $errs[] = array('otp_err',"Please Enter Your Temporary Password");
} 
else
if(isset($_REQUEST['voucherid'])) {
    
  // this section handles the post back into here
  $v->voucherid = strtoupper(trim($_REQUEST['voucherid']));
  
  /*
  // Check the type of voucher
  $patientIssued = $db->first_row("select 1
                                   from modcoupons c, modservices s 
                                   where c.voucherid=? 
                                   and c.svcnum = s.svcnum 
                                   and c.mcid = s.accid",array($v->voucherid));
  
  if($patientIssued != null) {
      header("Location: /acct/import_voucher_to_group.php?voucherid=".urlencode($v->voucherid));
      exit;
  }
   */
 }

if(isset($_REQUEST['otp'])) {
  $v->otp= trim($_REQUEST['otp']);
}

if(isset($_REQUEST['voucheridsearch']) ||  (isset($_REQUEST['voucherid']) && isset($_REQUEST['otp'])))
{
  $expected_chars = $GLOBALS['voucher_id_size'];
  if (strlen($v->voucherid)!=$expected_chars) $errs[] = array('voucherid_err',"Voucher ID must be $expected_chars uppercase letters");
  if(count($errs)==0)
  {
    // Check if the voucher already has password set
    $vouchers = $db->query("select u.mcid, u.sha1
                            from modcoupons v, users u
                            where v.voucherid = ? 
                            and v.status = 'accessed'
                            and u.acctype = 'VOUCHER'
                            and u.sha1 is NOT NULL
                            and u.mcid = v.mcid", array($v->voucherid));

    if(count($vouchers)>0) { // has password
      if($vouchers[0]->sha1 == User::compute_password($vouchers[0]->mcid, $v->otp)) {
        $user = User::load($vouchers[0]->mcid);
        $user->login('/acct/home.php');
        exit;
      }
      else // wrong password
        $errs[] = array('err','No vouchers match that voucher ID or Password');
    }

    $now=time();
    $vi = mysql_escape_string($v->voucherid);
    $vo = mysql_escape_string($v->otp);
    $result =sql("Select c.* from  modcoupons c, modservices s  where  c.voucherid = '$vi' and c.otp = '$vo' and
                  c.svcnum = s.svcnum ")
                 or die ("Cant query modcoupons ".mysql_error());
    $r2= mysql_fetch_object($result);
    if ($r2===false)
      $errs[] = array('err','No vouchers match that voucher ID or Password');
    //
    // if it has been revoked, then say so
    else
    if($r2->status=='revoked')
      $errs[] = array('err',"This voucher has been revoked. Please contact the issuer as indicated on your coupon");
    else
    if($r2->status!='completed' && $r2->status != 'accessed')
      $errs[] = array('err',"Your healthcare provider has not completed working on this voucher. Please contact the issuer as indicated on your coupon.");

    if(count($errs)==0) {
      $user = User::load($r2->mcid);
      $couponnum = $r2->couponum;

      if(($r2->patientprice==0)||($r2->paytype!='')) {
        if($user->acctype == 'VOUCHER') {
          $errs[] = array('err','No vouchers match that voucher ID or Password');
        }
        else
          $user->login("voucherhome.php?c=$couponnum");
      }
      else {
        $user->login("voucherclaimpay.php?c=$couponnum");
      }
    }
    
  }
  // okay there are errors, just fall ack into the regular code
}

//here
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];

echo <<<XXX
$header
<div id="ContentBoxInterior" mainTitle="Activate a MedCommons Voucher">
<h2>Activate a MedCommons Voucher</h2>

<div class=fform>
<form action=voucherclaim.php method=post>
<div class=inperr id=err>$v->err</div>

<div class=field><span class=n>Voucher ID</span>
<span class=q><input type=text name=voucherid value='$v->voucherid' /><span class=r>&nbsp;</span>
<div class=inperr id=voucherid_err>$v->voucherid_err</div></span>
</div>

<div class=field><span class=n>Password</span>
<span class=q><input type=password name=otp value='$v->otp' /><span class=r>&nbsp;</span>
<div class=inperr id=otp_err>$v->otp_err</div></span>
</div>

<div class=field><span class=n>&nbsp;&nbsp;</span>
<span class=q><input type=submit class='mainwide'
value='Claim Voucher' /><span class=r>&nbsp;</span></span>
</div>
</form>
</div>
<table class=tinst >
<tr ><td class=lcol >Instructions</td><td class=rcol >Please enter the Voucher ID and Password from your voucher:
<br/>For support please contact the issuer at the phone number on the voucher </td></tr>
</table>
</div>
$footer
XXX;
?>
