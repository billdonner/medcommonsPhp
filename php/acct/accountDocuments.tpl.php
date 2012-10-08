<?$types = array();?>
<style type="text/css">
  #accountDocumentsTable tbody tr td {
      text-align: left;
  }
</style>
<div style='text-align: center; min-width: 500px; max-width: 800px'>
<table class="stdTable" id="accountDocumentsTable" style='text-align: left;' cellspacing="3" cellpadding="2" width="100%">
  <thead>
  <tr><th class="tableLeft">Type</th><th>Comment</th><th>Date</th><th class="tableRight">&nbsp;</th></tr>
  </thead>
  <tbody>
  <?foreach($documents as $d):?>
    <?if(!isset($types[$d->dt_type])):?>
    <tr>
      <td><a href='<?=gpath("Commons_Url")."/gwredirguid.php?raw=true&guid=".$d->dt_guid."&dl=true&nopage=true"?>'><?=hsc(isset($TYPES[$d->dt_type])?$TYPES[$d->dt_type]:$d->dt_type)?></a></td>
      <td><?=$d->dt_type == "CURRENTCCR" ? "Current Health Record for ".$forUser->first_name." ".$forUser->last_name : hsc($d->dt_comment)?></td>
      <td><?=$d->dt_create_date_time?></td>
      <td>&nbsp;</td>
    </tr>
    <?$types[$d->dt_type]=true;?>
    <?endif;?>
  <?endforeach;?>
  <?if(count($documents)==0):?>
    <tr><td colspan="4" align="center">You do not have any account documents</td></tr>
  <?endif;?>
  </tbody>
</table>
</div>
