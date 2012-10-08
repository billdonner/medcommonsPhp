<?php
// this is almost the same as pickuprecords, but instead, it connects to existing ROI requests

//require_once "mc.inc.php";
require_once "site_config.php";
require_once "voucher_host.inc.php";

$VOUCHER_ID_SIZE=7;

function onpost()
{
  global $SOLOHOST;
  global $SOLOPROTOCOL;
  global $VOUCHER_ID_SIZE;
  // figure out where to go based on roireqid
  $errs = array();

  $host = $_SERVER['HTTP_HOST'];
  $roireqid=trim($_POST['roireqid']);


  if (strlen($roireqid)!=$VOUCHER_ID_SIZE) $errs[] = array('roireqid_err',"roireq ID must be $VOUCHER_ID_SIZE uppercase letters");
  if (count($errs)>0) return $errs;

  $redirserver = locate_voucher($roireqid);
  
  //if (!isrealappliance($redirserver))$errs[] = array('voucherid_err',"Invalid Voucher ID");
  
    if (count($errs)>0) return $errs;

  header ("Location: $redirserver/mod/roireq.php?reqid=$roireqid");
  die("Location: $redirserver/mod/roireq.php?reqid=$roireqid");
}

// start here
$v = new stdClass;
$v->err = $v-> roireqid_err = $v->otp_err = $v->otp = $v->roireqid = '';

// error check these args

$errs =array()  ;
if (isset($_REQUEST['roireqid'])){
$roireqid=trim($_REQUEST['roireqid']);  // bill, allow this on url as extra arg, ,specifically from emails
if (strlen($roireqid)!=$VOUCHER_ID_SIZE) $errs[] = array('roireqid_err',"roireq ID must be $VOUCHER_ID_SIZE uppercase letters");}
else $roireqid='';

if (isset($_POST['repost'])) 
{
	if (count($errs)==0)  onpost($roireqid);// doesnt return
}

if (count($errs)!=0) 
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];


$content =  <<<XXX
<div id="ContentBoxInterior" mainTitle="View Patient ROI Request">
<h2>View Patient ROI Request</h2>

<div class=fform>
<form action=pickuproireq.php method=post>
<input type=hidden value=repost name=repost />
<div class=inperr id=err>$v->err</div>

<style type='text/css'>
  table.roitable {
    margin-left: 17em;
  }
  table.roitable td {
    padding: 8px;
  }
</style>
<table class='roitable'>
  <tr><td>ROI Request ID</td><td><input type=text name=roireqid value='$roireqid' /></td></tr>
  <tr><td>&nbsp;</td><td><input type=submit class='mainwide' value='View Request' /></td></tr>
</table>
</form>
</div>
<table class=tinst >
<tr ><td class=lcol >Instructions</td><td class=rcol >Please enter the REQUEST ID from the Patient printout
<br/>If you have a MedCommons Account, you can incorporate this request directly into a <a href='/mod/vouchersetup.php?roi=$roireqid'>MedCommons Voucher</a></td></tr>
</table>
</div>
XXX;
// ok, we've made the body, throw the standard stuff around it
 require_once 'render.inc.php';
renderas_webpage($content);

?>
