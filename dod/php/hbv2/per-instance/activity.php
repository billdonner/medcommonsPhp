<?
/**
 * Lists out events in the activity log for the loaded user
 *
 * @params $u - user to display for
 * @params $sessions - activity log sessions
 */
?>
<p>Activity for user <?=$u->mcid?></p>
<ul>
<?foreach($sessions as $s):?>
  <li><p><?=strftime("%m-%d-%Y %H:%M:%S",$s->beginTimeMs/1000)?> - <?=$s->summary->description?> - <?=$s->summary->sourceAccount->id ?> ( <?=$s->summary->sourceAccount->idType?> )</p></li>
<?endforeach;?>
</ul>
