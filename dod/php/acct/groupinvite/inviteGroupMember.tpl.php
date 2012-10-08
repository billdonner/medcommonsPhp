<?if(isset($msg)):?>
  <p style="color: green;"><?=hsc($msg)?></p>
<?endif;?>
<h4>Group Members: &nbsp;<?=$practice->practicename?></h4>
<p>This group has the following members:</p>
<table id="groupMembers">
  <tr><th>Name</th><th>Email</th><th>Admin</th><th>&nbsp;</th></tr>
  <?$count = 0;?>
  <?foreach($members as $m):?>
    <tr>
      <td class="nm"><?=hsc($m->first_name)?> <?=hsc($m->last_name)?></td>
      <td class="em"><?=hsc($m->email)?></td>
      <td class="adm">
        <?if($m->adminaccid):?>
          <img src="../images/blacktick.gif"/>
        <?else:?>
          &nbsp;
        <?endif;?>
      </td>
      <td class="rm">
        <?if(($m->mcid == $accid) || ($practice->adminaccid = $accid)):?>
        <a href="removeGroupMember.php?accid=<?=$m->mcid?>&pid=<?=$practice->practiceid?>">remove</a>
        <?else:?>
          &nbsp;
        <?endif;?>
      </td>
    </tr>
    <? $count++;?>
    <?if($count > 5):?>
      <tr><td colspan="2"><?=count($members) - $count?> more ...</td></tr>
      <?break;?>
    <?endif;?>
  <?endforeach;?>
</table>
<?if(isset($badEmail)):?>
  <span style="color: red;">  
    <p>The email address you entered did not appear to be valid.</p>
    <p>Please enter a valid email address and try again.</p>
    <script type="text/javascript">focusOnLoad('email');</script>
  </span>
<?else:?>
  <p>You may invite another user to join this group by entering their email address below:</p>
<?endif;?>

<form name="groupInviteForm" method="post">
  <table>
    <tr><th>Email Address:</th><th><input type="text" name="email" size="40"/></th></tr>
    <tr><td>&nbsp;</td><td><input type="submit" name="send" size="40" value="Invite" title="Invite user to join group"/></td><td></tr>
  </table>
</form>
</div>
