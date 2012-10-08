<?php

//require_once "mc.inc.php";
require_once "site_config.php";
require_once "voucher_host.inc.php";

$VOUCHER_ID_SIZE=7;

function onpost()
{
  global $SOLOHOST;
  global $SOLOPROTOCOL;
  global $VOUCHER_ID_SIZE;
  // figure out where to go based on voucherid
  $errs = array();

  $host = $_SERVER['HTTP_HOST'];
  $voucherid=trim($_POST['voucherid']);
  $otp = $_POST['otp'];

  if (strlen($voucherid)!=$VOUCHER_ID_SIZE) $errs[] = array('voucherid_err',"Voucher ID must be $VOUCHER_ID_SIZE uppercase letters");
  
  
  $redirserver = locate_voucher($voucherid);
  
 // if (!isrealappliance($redirserver))$errs[] = array('voucherid_err',"Invalid Voucher ID");
  
  if (count($errs)>0) return $errs;


  header ("Location: $redirserver/mod/voucherclaim.php?otp=$otp&voucherid=$voucherid");
  die("Location: $redirserver/mod/voucherclaim.php?otp=$otp&voucherid=$voucherid");
}

// start here
$v = new stdClass;
$v->err = $v-> voucherid_err = $v->otp_err = $v->otp = $v->voucherid = '';

// error check these args

$errs =array()  ;
if (isset($_REQUEST['voucherid'])){
$voucherid=trim($_REQUEST['voucherid']);  // bill, allow this on url as extra arg, ,specifically from emails
if (strlen($voucherid)!=$VOUCHER_ID_SIZE) $errs[] = array('voucherid_err',"Voucher ID must be $VOUCHER_ID_SIZE uppercase letters");}
else $voucherid='';

if(isset($_POST['otp'])) {
  $otp = $_POST['otp'];	
} 
else 
  $otp='';

if (isset($_POST['repost'])) 
{
	if (count($errs)==0)  onpost($voucherid,$otp);// doesnt return
}

if (count($errs)!=0) 
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];


$content =  <<<XXX
<div id="ContentBoxInterior" mainTitle="Activate a MedCommons Voucher">
<h2>Activate a MedCommons Voucher</h2>

<div class=fform>
<form action=pickuprecords.php method=post>
<input type=hidden value=repost name=repost />
<div class=inperr id=err>$v->err</div>

<div class=field><span class=n>Voucher ID</span>
<span class=q><input type=text name=voucherid value='$voucherid' /><span class=r>&nbsp;</span>
<div class=inperr id=voucherid_err>$v->voucherid_err</div></span>
</div>

<div class=field><span class=n>Temporary Password</span>
<span class=q><input type=text name=otp value='$otp' /><span class=r>&nbsp;</span>
<div class=inperr id=otp_err>$v->otp_err</div></span>
</div>

<div class=field><span class=n>&nbsp;&nbsp;</span>
<span class=q><input type=submit class='mainwide'
value='Claim Voucher' /><span class=r>&nbsp;</span></span>
</div>
</form>
</div>
<table class=tinst >
<tr ><td class=lcol >Instructions</td><td class=rcol >Please enter the Voucher ID and Temporary Password from your voucher:
<br/>For support please contact the issuer at the phone number on the voucher. </td></tr>
</table>
</div>
XXX;
// ok, we've made the body, throw the standard stuff around it
 require_once 'render.inc.php';
renderas_webpage($content);

?>
