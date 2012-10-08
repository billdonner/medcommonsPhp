<h3>Welcome to the <span style='color: #080;'><?=hsc($p->practicename)?></span> Group.</h3>
<?if(isset($msg)):?>
<p style="color: red;"><?=$msg?></p>
<?endif;?>
<p>Apologies, this page is still under construction.</p>
<?/*
<p>To proceed with joining the group, please log in to the invited account below:</p>
<script type="text/javascript">
</script>
<form name="accountForm" 
      method="post"
      action='<?=$g['Identity_Base_Url']."/login"?>'>
  <input type="hidden" name="next" value="<?=detrail($g['Accounts_Url'])."/groupinvite/verifyJoin.php?join=true&a=$accid&e=".urlencode($email)."&h=$hmac"?>"/>
  <div style="width: 100%; margin-left: 20px;">
    <div id="idemailBox" style="margin-left: 50px;">
      <table>
        <tr><td>Email Address:</td><td>&nbsp;<input type="text" style="background-color: #f3f3f3; color: #777;" name="mcid" readonly="true" id="idemail" size="40" value="<?=$email?>"/></td></tr>
        <tr><td>Your Password:</td><td>&nbsp;<input type="password" name="password" size="20"/></td></tr>
      </table>
    </div>
    <p><input type="submit" name="proceed" title="Click to Continue to Sign up to Group" value="Proceed to Join Group"/></p>
  </div>
</form>
<iframe style="display: none;" name="login" src="">Your browser does not support iframes!</iframe>
*/?>
