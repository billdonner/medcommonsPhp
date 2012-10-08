<?
  global $Secure_Url;
?>
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
    <style type='text/css'>
      p {
          margin: 0.08in 0px;
      }
      #pageBreakContainer {
          vertical-align: middle;
      }
      table#badgeTable tr td {
        padding: 0.3in 0.5in;
      }

    </style>
</head>
<body <?if(req('print')!="no"):?>onload="window.print();"<?endif;?> >
<?  if($createCover) { ?>

<div id="page2">
    <hr style="margin: 1.0in 0px 0px 0px; "/>

    <table id='badgeTable'>
      <tr>
      <td>
        <span class="importantnumber">
        <?=hsc($u->first_name)?> <?=hsc($u->last_name)?>
        </span>
        <p><?=$prettyAccid?></p>
        <p><?=rtrim($Secure_Url,"/")."/".$accid ?></p>
        <p><img src='images/mc_logo_182x34.png'/></p>
      </td></tr>
    </table>
    <div id="pageBreakContainer">
      <div class="pageBreakDiv">&nbsp;</div>
      <div style="float: left; position: relative; top: 5px;">Fold</div>
      <div class="pageBreakDiv">&nbsp;</div>
    </div>
    <div style="position: relative">
      <img style="margin-right: 0px;" src="<?echo $barImgUrl?>"/>
      <div style="display: inline; position: absolute; left: 2.0in; top: 0.3in">
      <div style="float: left; font-weight: bold;">
        PRIVATE FAX<br/>
        COVER SHEET
      </div>
        <div style="height: 0.8in; position: absolute; left: 0.0in; top: 0.8in " >
        <table style="width: 2.3in">
          <tr><td colspan='2' class='smallinstructions'>
               INSTRUCTIONS: This PHR account accepts FAX and converts them to PDF files.
          </td></tr>
          <tr><td style="width:0.6in;">FAX TO:</td><td style="text-align: right;">
           <span class="importantnumber"><? if($accid=="9999999999999999"){?>1 (555) 555-5555<?}else{?>1 (877) 717-7503<?}?>&nbsp;</span>
          </td></tr>
        </table>
      </div>
    </div>

      </div>
    <hr style="clear: both; margin-bottom: 1.3in;"/>

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
