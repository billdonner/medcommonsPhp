<?php 
global $Secure_Url;
?>
<style type='text/css'>
  @import url('dod.css');
</style>
<?
if(isset($GLOBALS['DEBUG_JS'])) {
  echo "<script type='text/javascript' src='MochiKitDebug.js'></script>";
  echo "<script type='text/javascript' src='sha1.js'></script>";
  echo "<script type='text/javascript' src='utils.js'></script>";
  echo "<script type='text/javascript' src='ajlib.js'></script>";
  echo "<script type='text/javascript' src='contextManager.js'></script>";
}
else
  echo "<script type='text/javascript' src='acct_all.js'></script>";
?>

<div id='upload'>

<h2>DICOM On Demand Image Upload</h2>

<p>This page allows you to upload image data for use by a doctor or support.
You will receive a Voucher ID and PIN to give to the recipient to allow them 
to access the data.</p>

<? include "detectDDL.tpl.php";?>
    
<h3 id='fillOutFormHeading'>Step 2 - Fill Out Details &nbsp;<img class='hidden' src='images/greentick.gif'/></h3>
<div id='fillOutFormStep' class='hidden section'>
    <p>To proceed, please fill out the following form. All items are Optional:</p>
    <form name='dicomUploadForm' id='dicomUploadForm' method='POST'>
       <table id='uploadTable'>
         <tr><th>Email Address:</th><td><input type='text' name='email' title='An email address where we can contact you'/></td></tr>
         <tr><th>Referring Physician:</th><td><input type='text' name='physician'/></td></tr>
         <tr><th>Procedure</th><td><input type='text' name='procedure'/></td></tr>
         <tr><th>Type</th><td><input type='text' name='procedureType'/></td></tr>
         <tr><th>Accession #:</th><td><input type='text' name='accessionNumber'/></td></tr>
         <tr><th>Relevant History:</th><td><textarea name="history" rows="5"></textarea></td></tr>
         <tr><th>&nbsp;</th><td><button id='continueSubmitFormButton' onclick='blindUp("fillOutFormStep",{duration:0.5}); blindDown("selectDataStep",{duration:0.5}); removeElementClass($$("#fillOutFormHeading img")[0],"hidden"); return false;' >Continue</button></td></tr>
        </table>
    </form> 
</div>

<h3 id='selectDataHeader'>Step 3 - Select Your Data &nbsp;<img class='hidden' src='images/greentick.gif'/></h3>
<div id='selectDataStep' class='hidden section'>
    <p>The next step is to select the files, folder or CD that you want to upload.</p>
    <p>Click below to browse to find the CD or
       folder containing the images you want to upload and select 'OK' to begin uploading it.</p>
     <p><input id='submitUploadForm' type='button' value='Click Here to Select Files to Upload'  disabled='true'/> </p>
     <p id='transferError' class='error hidden'>&nbsp;</p>
</div>

<!-- 
<h3 id='healthurlStepHeader'>Step 4 - Print or Record You Voucher Details &nbsp;<img class='hidden' src='images/greentick.gif'/></h3>
-->
<h3 id='voucherDetailsStepHeader'>Step 4 - Print or Record Your Voucher Details &nbsp;<img class='hidden' src='images/greentick.gif'/></h3>
<div id='voucherDetailsStep' class='hidden section'>
   
    <p>Your patient data is stored in a secure HealthURL with the following details:</p>
    
    <table id='voucherTable'>
	    <tr><th>Patient:</th><td id='patientName'>Please Wait</td></tr>
	    <tr><th>Voucher:</th><td id='voucherId'>Please Wait</td></tr>
	    <tr><th>PIN:</th><td id='voucherPin'>Please Wait</td></tr>
	    <tr><th>Access URL:</th><td><a id='healthurl' target='ccr' title='Your patient data on MedCommons - Click to Review' href=''></a></td></tr>
	    <tr><th>Progress:</th>
		    <td><span id='progress'>Please Wait</span>
             </td>
        </tr>
	    <tr><th>&nbsp;</th><td class='buttons'>
		    
            <span id='cancelUpload' class='hidden'><button id='cancelUploadButton'>Cancel</button></span>
		    <button id='printButton'>Print</button> <button id='restartButton' class='hidden'>New Upload</button>
	    </td></tr>
    </table>
    
    <p id='transferError2' class='error hidden'>&nbsp;</p>
    
    <p>Thank you for using MedCommons!</p>
    
</div>

<?include "problemReport.tpl.php"; ?>  

</div>
<iframe name='printFrame' id='printFrame' style='position: absolute;  left: -500px; top: -500px; width: 400px; height: 500px;' src='about:blank'>
</iframe>
<script type='text/javascript'>
  var localGatewayRootURL = '<?=$Secure_Url?>';
  <? include "required_dod_ddl_version.tpl.php";?>
</script>
<script type='text/javascript' src='dod.js'> </script>
<script type='text/javascript'>
  <? include "required_dod_ddl_version.tpl.php";?>
</script>