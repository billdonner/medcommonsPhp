<?php

require_once 'login.inc.php';
require_once 'urls.inc.php';
require_once 'settings.php';
require_once 'utils.inc.php';
require_once 'db.inc.php';


require_once "modpay.inc.php";
$formerr=''; $disabled = true;
list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();
//list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();
$v = new stdClass;
$v->err = $v-> accesscode_err = $v->secretkey_err = $v->secretkey = $v->accesscode =$v->couponum = $v->otp =  '';

// error check these args
$errs = array (); $errstring ='';
// if rep;osting then check that both passwords match

if (isset($_POST['repost']))
{
  $v->couponum = $_POST['repost'];

  $pass1 = $_POST['newp'];
  $pass2 = $_POST['newp2'];

  $db = DB::get();
  $c = $db->first_row("select * from modcoupons c where c.couponum = ?",array($v->couponum));
  $accid = $c->mcid;

  if(strlen($pass1) < 5) {
    $formerr = 'Please use a password of more than 5 characters in length';
  }
  else
  if ($pass1 != $pass2) {
    $formerr = 'Passwords do not match'; 
  }
  else {
    $disabled = false; 
    
    $sha1 = User::compute_password($accid, $pass1);
    $db->execute("update users set sha1=?, acctype='VOUCHER' where mcid=?",array($sha1,$accid));

    // ensure accessed state is set. 
    $db->execute("update modcoupons set status='accessed' where couponum=?",array($v->couponum));

    $user = User::load($accid);
    $user->login('/acct/home.php');
  }
}
else
if(isset($_GET['c'])) {
  $v->couponum = $_GET['c'];
}
else 
  die ('No coupon number specified');

$now=time();

$result =sql("select * from  modcoupons c, modservices s  where  c.couponum = '$v->couponum' and c.svcnum=s.svcnum ")
or die ("Cant query modcoupons ".mysql_error());
$r2= mysql_fetch_object($result);
if ($r2===false)
die('No vouchers match that voucher ID or One Time Password');

//
// if it has been revoked, then say so

if ($r2->status=='revoked') {header("Location: voucherclaim.php"); die("Revoked voucher");} 

//
// check to make sure it is paid
// if (!isset($_GET['p'])) sql("Update modcoupons set status='accessed' where couponum='$v->couponum'  ");

$website=$GLOBALS['mod_website'];

if($disabled) {
  $signin = '<span class=dimmed>Sign In</span>'; 
}
else  {
  $signin =<<<XXX
  <a href='${website}personal.php' onclick='document.signin.submit(); return false;'>Sign In</a>
XXX;
}

if ($disabled) $openhurl = '<span class=dimmed>Open CCR</span>'; else 
$openhurl = <<<XXX

<a  target='_new' title='open records in new window' 
href='$r2->hurl' onclick='return open_hurl($r2->couponum)'>
<img border=0 src='/images/icon_healthURL.gif' alt=hurlimg />Open CCR</a>
XXX;


if ($disabled) $changepass = '<span class=dimmed>Change Password</span>'; else 
$changepass = <<<XXX
<a href=/acct/settings.php >Change Password</a>
XXX;


$shaotp = sha1($r2->otp);

$body = <<<XXX
<div class=homecoupon>
<h2>HIPAA Transfer Agreement</h2>
<p>
$fn $ln, your voucher service is Complete and ready to transfer to your control.
</p><p>
By accepting this agreement you acknowledge that the issuer is providing a personal copy of your information as required by the HIPAA Privacy Rule and is not responsible for the security 
and privacy of this information now under your control. This voucher expires **SXD**
The issuer of this voucher and you will continue to have access to this voucher until it expires. 
This agreement is covered by the <a href='http://www.medcommons.net/termsofuse.php'>MedCommons Terms of Use.</a>
</p>
<div class=fform >
<form action=voucherhome.php method=post>
<input type=hidden name=repost value=$r2->couponum />
<div class=inperr id=err>$formerr</div>
<div class=field><span class=n>New Password</span><span class=q><input type=password size=20 name=newp value='' /><span class=r>&nbsp;</span></div>

<div class=field><span class=n>Repeat Password</span>
<span class=q ><input type=password size=20 name=newp2 value='' /><span class=r> </span></div>
<div class=field><span class=n>&nbsp;&nbsp;</span><span class=q><input type=submit class='primebutton' value='I Agree' />
<input type=submit value='Cancel' class='altshort' name='cancel' /></span></div>

</form>
</div>
<hr width=20%>
<small>
Service: **SPN** **HD** Description **SPN** Status: **SPS** VoucherID: **VI** Price: USD  **SPR** 
</small>
</div>
XXX;


$header = page_header_nonav("page_preview","HIPAA Transfer Agreement  - MedCommons on Demand" );

$footer = page_footer();
$markup = '<div id="ContentBoxInterior" mainTitle="AccessMedCommons Voucher" >'."
<script type='text/javascript'>
  function open_hurl(cnum) {
    window.open('hurlredir.php?cnum='+cnum+'&c=v');
    return false;
  }
  </script>
  $body</div>";



$markup = standardcoupon ($r2->couponum,$markup);


echo $header.$markup.$footer;
?>
