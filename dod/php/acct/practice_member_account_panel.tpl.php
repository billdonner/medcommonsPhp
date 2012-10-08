<div class="featurebox" id="worklist">
    <div id="worklistButtons">
<?if($info->enable_vouchers):?>
  &nbsp;
<?endif;?>
    </div>
  <?
     $_REQUEST['pid']=$info->practice->practiceid;
     $_REQUEST['widget']=true;

     # Forward to real page
     include "rls.php";
  ?>
</div>
