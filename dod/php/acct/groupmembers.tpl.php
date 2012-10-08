<?
/**
 * Group members page - displays array of group/user info
 * see GxGroup.php
 *
 * @author ssadedin
 */
?>
<div style="text-align: left; padding-left: 10px;">
<h3>Members of your Group</h3>
<br/>
<table id="membersTable" class="stdTable">
  <thead>
    <tr><th>Account Id</th><th>Name</th><th>Email</th></tr>
  </thead>
  <?foreach($rows as $u):?>
    <tr>
      <td><?=$u['mcid']?></td>
      <td><?=$u['first_name']." ".$u['last_name']?></td>
      <td class="mbrEmail"><a href='mailto:<?=$u["email"]?>'><?=$u["email"]?></a></td>
    </tr>
  <?endforeach;?>
</table>
</div>
