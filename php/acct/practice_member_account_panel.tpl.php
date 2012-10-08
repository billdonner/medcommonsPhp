<?
  require_once "alib.inc.php";

  $info = get_validated_account_info();
  $gwUrl = allocate_gateway($info->accid);
  $isDDLRunning = false;
  if($isDDLRunning) {
    $uploadURL = "http://localhost:16092/CommandServlet?command=upload";
  }
  else
    $uploadURL = $gwUrl."/ddl/start?auth=".$info->auth;
?>
<div class="featurebox" id="worklist">
    <div id="worklistButtons">
          <a id='newDICOMLink' href="<?=$uploadURL?>" title="DICOM" onclick='displayGroupMessages(); startWatchingDDL("<?=$gwUrl?>");'
             ><img alt='Start DDL' src='images/upload.png' />&nbsp;&nbsp;DICOM</a>
         <?if(!$info->enable_dod):?>
          <a href="<?=new_ccr_url($info->accid,$info->auth)?>" title="Create New Patient Account"
             target="ccr"><img alt='new patient account' src='images/folder.png' />&nbsp;&nbsp;New Patient</a>
          <?endif;?>
          <?if($info->enable_vouchers):?>
            &nbsp;
            <span>
                  Request ID <input type='text' id='roirid'/> 
                  <img id='searchROIRImg' title='Look up Request / Voucher ID' src='images/magnifier.png'/>
            </span> 
          <?endif;?>
    </div>
  <?
     $_REQUEST['pid']=$info->practice->practiceid;
     $_REQUEST['widget']=true;

     # Forward to real page
     include "rls.php";
  ?>
</div>
<?if(req('voucherid')):?>
<script type='text/javascript'>
addLoadEvent(function() {
	showVoucher('', '<?=htmlentities(req('voucherid'),ENT_QUOTES)?>');	
});
</script>
<?endif;?>
