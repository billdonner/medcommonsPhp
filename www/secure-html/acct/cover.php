<?header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?
  /*
    This page renders a fax cover sheet for a given PIN/track# that the
    user provides.
  */
?>
<?
require_once "dbparamsidentity.inc.php";

  function err($msg) {
    echo "<html><body><p>An error occurred trying to fulfill your request.  
      The following message was returned:<br/><br/><pre>$msg

".mysql_error()."
      </pre></body></html>";
    exit;
  }
  
  // db connect
  $db=$GLOBALS['DB_Database'];
  mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password']) or die ("can not connect to mysql");
  mysql_select_db($db) or die ("can not connect to database $db");

  // Figure out request params
  $accid = preg_replace("/ /","",$_REQUEST['accid']);
  $prettyAccid = substr($accid,0,4)." ".substr($accid,4,4)." ".substr($accid,8,4)." ".substr($accid,12,4);
  $coverPin = $_REQUEST['coverPin'];
  $coverProviderCode = $_REQUEST['coverProviderCode'];
  if((!$coverProviderCode) || ($coverProviderCode == ''))
    $coverProviderCode = null;

  $encryptedPin = sha1($coverPin);

  if($_REQUEST['createCover']) {
    $createCover = true;
    if($coverPin=="") {
      $coverPin = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }
  }

  $coverNotifyEmail  = $_REQUEST['coverNotifyEmail'];

  // Add row to fax cover table
  $result = mysql_query("insert into cover (cover_id, cover_account_id, cover_notification, cover_encrypted_pin, cover_provider_code)
                         values(NULL, '$accid','$coverNotifyEmail','$encryptedPin',".($coverProviderCode ? "'$coverProviderCode'" : "NULL"). ")");
  if(!$result) {
    err("Unable to add new cover in database");
  }

  $coverId = mysql_insert_id();

  // Calculate the bar code url
  $barcode="MC/$coverId/"; 
  $barImgUrl="https://secureservices.dataoncall.com/CreateBarCode.serv?BARCODE=$barcode&CODE_TYPE=DATAMATRIX&DM_DOT_PIXELS=10";
?>
<!-- 
  Fax Cover Sheet
  Copyright MedCommons 2006
 -->
<html>
<head>
    <title>MEDCOMMONS PATIENT INFORMATION PRACTICES REQUEST AND CONSENT</title>
    <link rel="stylesheet" type="text/css" href="cover.css"/>
    <script type="text/javascript" src="MochiKit.js"> </script>    
</head>
<body onload="window.print();">
<div style="margin-bottom: 0.4in; text-align: right">
  <img width="246" height="50" src="images/MEDcommons_logo_246x50.gif"/>  
</div>
<h1>PATIENT’S INFORMATION PRACTICES REQUEST AND CONSENT</h1>
<br style="height: 0.2in"/>

<?if($coverNotifyEmail != "") {?>
<div style="position: absolute; left: 4in;">
     <span style="padding-bottom: 2px; border-bottom: solid 1px black;">&nbsp;&nbsp;<?echo $coverNotifyEmail?>&nbsp;</span>
     <div class="annotation" style="padding-top: 5px;">Practice Notification Email</div>
</div>
<?}?>

Dear <span style="margin-bottom: 2px; padding-bottom: 2px; border-bottom: solid 1px black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<div style="float:left; width: 0.4in;">&nbsp;</div><div class="annotation" style="padding-top: 5px;">Doctor or Practice Name</div>

    
<p>I maintain a standard Personal Health Record (PHR) to help keep my care safe
and effective. Your cooperation in using and updating my PHR is greatly
appreciated. </p>

<p>My PHR ID is   <span class="importantnumber"><?echo $prettyAccid ?></span>   at http://www.MedCommons.net</p>

<?  if($createCover) { ?>
<p>Use PIN &nbsp;&nbsp;<span class="importantnumber"><span class="ul"><?echo $coverPin[0]?></span>&nbsp;<span class="ul"><?echo $coverPin[1]?></span>&nbsp;<span class="ul"><?echo $coverPin[2]?></span>&nbsp;<span class="ul"><?echo $coverPin[3]?></span>&nbsp;<span class="ul"><?echo $coverPin[4]?></span>&nbsp;</span> &nbsp;to secure our messages and PHR updates.<br/>
</p>
<? } ?>

<p>Please save this request and consent in your files. <?  if($createCover) { ?>Using the PIN, you will
be able to review and update my PHR online using CCR-standard and PDF
documents.<? } ?> This PHR account may also accept FAX and DICOM diagnostic images
(see below).  You may use my MedCommons account as a HIPAA and FDA compliant
secure communication system.</p>

<p>Thank you,<br/>
<br/>
<br/>
<div style="margin: 0px 0px 6px 0px;">Signed: &nbsp; ___________________________  &nbsp;&nbsp;&nbsp;_______________</div>
<div style="float:left; width: 4em;">&nbsp;</div><div class="annotation" style="float: left;">Patient:</div><div class="importantinfo" style="float: left; width: 2in;">&nbsp;</div><span class="annotation">Date</span>
<?  if($createCover) { ?>
  <p style="page-break-after: always;">&nbsp;</p>

  <hr style="margin: 15px 0px 0px 0px; "/>
  <span class="importantnumber">MedCommons Account <?echo $prettyAccid ?></span>

  <div style="position: relative">
  <img style="margin-right: 0px;" height="200px;" src="<?echo $barImgUrl?>"/>
  <div style="display: inline; position: absolute; left: 2in; top: 0.3in">
  <div style="float: left; font-weight: bold;">PRIVATE FAX<br/>COVER SHEET</div>
    <div style="float: right; margin-right: 0.1in; height: 1in;">
      Number of Pages:  _______________ <br/><br/>
      <table style="width: 2.7in">
        <tr><td style="width:1in;">FAX TO:</td><td style="text-align: right;"><span class="importantnumber">877 - 717 - 7503&nbsp;</span></td></tr>
      </table>
    </div>
    <div class="smallinstructions" style="width: 4.7in;">FAX INSTRUCTIONS: This PHR account accepts FAX and converts them to PDF
    files. User must use a copy of this page as the cover sheet. You can print
    more PRIVATE FAX COVER SHEETS for this PHR at any time by entering my ID at
    www.MedCommons.net, selecting Print FAX Covers and entering our shared
    PIN.</div>
  </div>

    </div>
  <hr style="clear: both;"/>
  <p class="smallinstructions"><b>PRACTICE ADMINISTRATOR NOTE:</b> MedCommons is a patient-centered, HIPAA
  compliant, secure communications and personal health record service that we
  hope will make your practice more efficient while providing valued patient
  privacy and consumer empowerment features. Secure communications and simple
  Emergency PHRs are free to both your practice and your patients and we hope you
  will recommend them to all. The PIN privacy system keeps MedCommons costs low
  and enables our service to be universally accessible to all practices and
  patients regardless of means. Busy practices that find PINs inconvenient can
  enable single sign-on for their staff and can sponsor FAX, practice portal and
  PHR storage accounts by contacting us at info@medcommons.net or 800-555-1212.
  </p>
<? } ?>

</body>
</html>

