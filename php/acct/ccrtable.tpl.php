<?require_once "ccrloglib.inc.php";?>
<h4>CCRs for Account <?=prettyaccid($accid)?></h4>
<table id='ccrTable' class="stdTable" cellspacing='2' width="100%">
  <thead>
<?/*
  <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td align='right'><?=$pageLinks?></td></tr>
*/?>
  <tr>
    <th title='Date and Time'>Date/Time</th>
    <th title='Tracking Number'>Tracking Number</th> 
    <th title='To'>To</th>
    <th title='Subject'>Subject</th>
  </tr>
  </thead>
  <tbody>
<?
  $odd = false;
  foreach($rows as $r) {
    $odd = (!$odd); // flip polarity
?>
    <tr class='<?=$odd?"oddRow":"evenRow";?>'>
      <td class="left"><?=$r->date?></td>
      <td><a href="<?=$g['Commons_Url']."gwredirguid.php?guid=".$r->guid?>" target="ccr"><?=$r->tracking?></td>
      <td title='To'><?=$r->dest != null?$r->dest : "&nbsp;"?></td>
      <td><?=htmlspecialchars($r->subject)?></td>
    </tr>
<? } ?>
</tbody></table>
