<?
require_once "alib.inc.php";
global $Secure_Url;
?>
<div id="worklistButtons">
  <a href="<?=new_ccr_url($info->accid,$info->auth)?>" title="Create New Patient Account"
  target="ccr"><img alt='new patient account' src='<?=$Secure_Url?>/images/b_npa.jpg' /></a>
</div>
