<?          
  if($activeGateway) {
    dbg("active gw");
    $displayUrl = $activeGateway."/CurrentCCRWidget.action?combined&accid=".$info->accid."&auth=".$info->auth."&returnUrl=".urlencode(gpath('Accounts_Url')."/clearPatientDetails.php?cleargw=true");
  }
  elseif($cccrGuid && $patientMode) {
    dbg("cccr and patient mode ");
    $displayUrl = gpath('Commons_Url')."/gwredirguid.php?guid=$cccrGuid&nopage&dest=".urlencode("CurrentCCRWidget.action?combined&accid=".$info->accid);
  }
  else {
    $displayUrl = false;
  }
?>
<div id='patientDetails'>
  <?if($displayUrl):?>
    <span id="patientDetailFrameMarker"></span>
     <?=template("iframe.tpl.php")->set("name","patientDetailsFrame")->set("src",$displayUrl)->set("height",1000)->fetch()?>
  <?else:?>
    <?if($info->practice && !$patientMode):?>
      <?if(isset($patientCount) && ($patientCount==0)):?>
      <div class='dashboardmsg'>
        <p style='margin-bottom: 10px;'><img style='position: relative; left: -3px; top: 3px;' src='images/infoicon.png'/> 
            You don't have any patients yet.  To get started, try the following: 
        </p>
        <p>
           
           <ul class='normal' style='padding-left: 20px;'>
           <?if($info->enable_dod):?>
	           <li>Click on the 'DICOM' link above to start the DDL Service and upload image data from your computer.</li>
           <?else:?>
           <li>If you have a Request ID, 
               enter it in the box in the upper right.</li>
           <li>Temporary patient accounts can be
               created using the Services menu.</li>
           <?endif;?>
          </ul>
        </p>
        <br/>
      </div>
      <?else:?>
        <p>Click a patient name to open their Current CCR.</p>
      <?endif;?>
    <?else:?>
      <br/>
      <p>You do not yet have a Current CCR associated with your account.</p>
      <br/>
      <p>To get started, <a href="<?=new_ccr_url($info->accid,$info->auth, "new")."&am=p"?>" title="Create a new CCR" target="ccr">Create</a>
      or <a href="<?=gpath('Commons_Url')?>/gwredir.php?a=ImportCCR" title="Create a CCR by uploading a file" target="ccr">Import</a>
       a CCR, and set it as your Current CCR.</p>
    <?endif;?>
    <br/>
    <br/>
  <?endif;?>
</div>
