<h3>Welcome to the <span style='color: #080;'><?=hsc($p->practicename)?></span> Group.</h3>
<?if(isset($msg)):?>
<p style="color: red;"><?=$msg?></p>
<?endif;?>
<p>If you would like to join this group, please click the Proceed button below:</p>
<script type="text/javascript">
</script>
  <form name="accountForm" 
        method="post"
        action="verifyJoin.php">
  <input type="hidden" name="a" value="<?=$accid?>"/>
  <input type="hidden" name="h" value="<?=$hmac?>"/>
  <input type="hidden" name="e" value="<?=$email?>"/>
  <input type="hidden" name="join" value="true"/>
  <div style="width: 100%; margin-left: 20px;">
    <p><input type="submit" name="proceed" title="Click to Continue to Sign up to Group" value="Proceed to Join Group"/></p>
  </div>
</form>
