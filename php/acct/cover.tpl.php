<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- 
  Fax Cover Sheet
  Copyright MedCommons 2008
 -->
<html>
<head>
    <title>MEDCOMMONS PATIENT INFORMATION PRACTICES REQUEST AND CONSENT</title>
    <link rel="stylesheet" type="text/css" href="cover.css"/>
    <script type="text/javascript" src="MochiKit.js"> </script>    
</head>
<body <?if(req('print')!="no"):?>onload="window.print();"<?endif;?> >
<?if($no_cover_letter !== "true"):?>
<div id="page1">
  <div style="margin-bottom: 0.4in; text-align: right">
    <img src="<?= $acLogo ?>" />
  </div>
  <h1>PATIENT&#146;S INFORMATION PRACTICES REQUEST AND CONSENT</h1>
  <br style="height: 0.2in"/>

  <?if($coverNotifyEmail != "") {?>
  <div style="position: absolute; left: 4in;">
       <span style="padding-bottom: 2px; border-bottom: solid 1px black;">&nbsp;&nbsp;<?=hsc($coverNotifyEmail)?>&nbsp;</span>
       <div class="annotation" style="padding-top: 5px; text-align: center;">Notification Email</div>
  </div>
  <?}?>

  Dear &nbsp;&nbsp;&nbsp;<span style="font-weight: bold; margin-bottom: 2px; padding-bottom: 2px; border-bottom: solid 1px black;">
  <?if(($coverProviderCode!=null) && ($coverProviderCode!="")):?>
    &nbsp;&nbsp;<?=hsc($coverProviderCode)?>&nbsp;&nbsp;
  <?else:?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <?endif;?>
       </span>
  <div style="float:left; width: 0.6in;">&nbsp;</div><div class="annotation" style="padding-top: 5px;">Doctor or Practice Name</div>

      
  <p>I maintain a standard Personal Health Record (PHR) to help keep my care safe
  and effective. Your cooperation in using and updating my PHR is greatly
  appreciated. </p>

  <p>My PHR ID is   <span class="importantnumber"><?echo $prettyAccid ?></span>   at https://<?= $acDomain ?></p>

  <?  if($createCover && $coverPin) { ?>
  <p>Use PIN &nbsp;&nbsp;<span class="importantnumber"><span class="ul"><?echo $coverPin[0]?></span>&nbsp;<span class="ul"><?echo $coverPin[1]?></span>&nbsp;<span class="ul"><?echo $coverPin[2]?></span>&nbsp;<span class="ul"><?echo $coverPin[3]?></span>&nbsp;<span class="ul"><?echo $coverPin[4]?></span>&nbsp;</span> &nbsp;to secure our messages and PHR updates.<br/>
  </p>
  <? } ?>

  <p>Please save this request and consent in your files. 
  <?  if($createCover && $coverPin):?>Using the PIN, you will
  be able to review and update my PHR online using CCR-standard and PDF
  documents.<?endif;?> This PHR account may also accept FAX and DICOM diagnostic images
  (see below).  You may use my MedCommons account as a HIPAA and FDA compliant
  secure communication system.</p>

  <p>Thank you,<br/>
  <br/>
  <br/>
  <div style="margin: 0px 0px 6px 0px;">Signed: &nbsp; ___________________________  &nbsp;&nbsp;&nbsp;_______________</div>
  <div style="float:left; width: 4em;">&nbsp;</div><div class="annotation" style="float: left;">Patient:</div><div class="importantinfo" style="float: left; width: 2in;">&nbsp;</div><span class="annotation">Date</span>
  <br style='clear: both;'/>
  <div style='height: 50px;'/>
</div>
<div id="pageBreakContainer">
  <div class="pageBreakDiv">&nbsp;</div>
  <div style="float: left; position: relative; top: 5px;">Page Break</div>
  <div class="pageBreakDiv">&nbsp;</div>
</div>
<?endif;?>
<?  if($createCover) { ?>
<div id="page2">
    <p style="page-break-after: always;">&nbsp;</p>
    
    <hr style="margin: 15px 0px 0px 0px; "/>

    <span class="importantnumber">MedCommons Account <?echo $prettyAccid ?> 
      - <?=hsc($u->first_name)?> <?=hsc($u->last_name)?>
    </span>

    <div style="position: relative">
    <img style="margin-right: 0px;" src="<?echo $barImgUrl?>"/>
    <div style="display: inline; position: absolute; left: 2.2in; top: 0.3in">
    <div style="float: left; font-weight: bold;">
      PRIVATE FAX<br/>
      COVER SHEET
    </div>
      <div style="float: left; margin-left: 0.4in; height: 0.8in;">
        Number of Pages:  _______________ <br/><br/>
        <table style="width: 2.7in">
          <tr><td style="width:1in;">FAX TO:</td><td style="text-align: right;">
           <span class="importantnumber"><? if($accid=="9999999999999999"){?>1 (555) 555-5555<?}else{?>1 (877) 717-7503<?}?>&nbsp;</span>
          </td></tr>
        </table>
      </div>
      <div class="smallinstructions" style="width: 4.2in; clear: both;">FAX INSTRUCTIONS: This PHR account accepts FAX and converts them to PDF
      files.</div>
    </div>

      </div>
    <hr style="clear: both;"/>

    <?if($note):?>
      <p><b>USER NOTE:</b>&nbsp;&nbsp; <?=hsc($note)?></p>
    <?endif;?>

    <p class="smallinstructions"><b>PRACTICE ADMINISTRATOR NOTE:</b> MedCommons is a patient-centered, HIPAA
    compliant, secure communications and personal health record service that we
    hope will make your practice more efficient while providing valued patient
    privacy and consumer empowerment features.  For more information and the current 
    Terms of Use, please visit www.medcommons.net. 
    </p>
</div>
<? } ?>

</body>
</html>
