<?php
/**
 * Voucher completion
 *
 * Moves the specified voucher to completed status.  Supports 
 * both HTML and JSON formats via fmt parameter.
 */
require_once 'modpay.inc.php';
require_once 'JSON.php';
require_once 'utils.inc.php';
require_once 'db.inc.php';
require_once 'mc.inc.php';

$c = req('c');
$accid = req('accid');
$next = req('next');
$fmt = req('fmt');

try {
  $db = DB::get();
  if($c) {
    $r = $db->first_row( "select * from modcoupons c, modservices s  where couponum=? and c.svcnum = s.svcnum",array($c));
  }
  else
  if($accid) {
    if(!is_valid_mcid($accid,true)) 
      throw new Exception("Invalid value for parameter $accid");
    $r = $db->first_row( "select * from modcoupons c, modservices s where c.mcid=? and c.svcnum = s.svcnum",array($accid));
    if(!$r) 
      throw new Exception("Unknown voucher account $accid");
    $c = $r->couponum;
  }

  if($r!==false) {
    // if we dont find the record there is something really off
    sql("Update modcoupons set status = 'completed'  where couponum='$c'  ");

    // if we have an email address for the user, lets send an email
    if ($r->patientemail!='')
    {

      if ($GLOBALS['voucherid_solo']) $claimUrl = 'https://'.$_SERVER['HTTP_HOST'].'/pickuprecords.php'; else
      $claimUrl =$GLOBALS['voucher_pickupurl'] ; // $GLOBALS['mod_base_url']."/voucherclaim.php";
      $claimUrl .="?voucherid=$r->voucherid";
      $voucherexpirationdate=$r->expirationdate;

      $voucherpaystatus=paidvia($r->patientprice,$r->paytype);

      $voucherprice='0.00';
      $payin="There is no charge for this service.";
      if ($r->servicelogo!='') 
    $servicelogo = "<img src='$r->servicelogo' border=0 />"; else $servicelogo = '';
    
      if ($r->patientprice!=0)
      {
        $payin="These records have already been paid via $r->paytype";
        $voucherprice='$'.money_format('%i', $r->patientprice/100.);
        if ($r->paytype=='')
          $payin = " A payment of $voucherprice will be required.";
      }

      $supportphone = "";
      if($r->supportphone && ($r->supportphone != "")) {
         $supportphone = " on $r->supportphone";
      }
      if(isset($r->serviceemail) && ($r->serviceemail != ""))
        $from = "noreply@medcommons.net";
      else
        $from = $r->serviceemail;

      $msg = <<<XXX
  <b>Dear $r->patientname,</b>\n        
  <p>Your MedCommons voucher $r->voucherid for the service $r->servicename is now available to you.
  $payin
  </p>
  <p>Additional information:  $r->servicedescription $r->addinfo. 
  <p>Please pick up your records at $claimUrl using the password on the printed voucher.</p>\n
  <p>This temporary HealthURL will expire on $r->expirationdate. You will need to 
  view, print and save the content before the expiration date. You can aslo copy
  the contents to a permanent MedCommons account.  For questions, please contact the issuer$supportphone.</p>\n
  <p>Thank you,</p>
  <p><small>This mail produced by  <a href='https://www.medcommons.net/'>MedCommons on Demand</a></small></p>\n
XXX;
      $wrapped = <<<XXX
  <html>
  <head>
    <title>Voucher $r->voucherid from $r->servicename Complete </title>
  </head>
  <body>
  $msg
  </body>
  </html>
XXX;
      // To send HTML mail, the Content-type header must be set Bcc: billdonner@gmail.com
      $headers  = <<<XXX
MIME-Version: 1.0
User-Agent: MedCommons Mailer 1.0
Content-type: text/html; charset=iso-8859-1
To: $r->patientemail
From: MedCommons On Demand <$from>
Reply-To: $from
XXX;
      $status = mail($r->patientemail,"Your voucher for $r->servicename has been completed",$wrapped,$headers);
    }
  }

  if(!$fmt) {
    header("Location: $next");
    die ("redirecting to $next sql status is ".mysql_error());
  }
  else {
    echo "{status: 'ok'}";
  }
}
catch(Exception $e) {
  $msg = $e->getMessage();
  if($fmt && ($fmt=="json"))
    echo "{status: 'failed', message: '$msg'}";
  else {
    error_page("Failed to complete voucher.",$e);
  }
}
?>
