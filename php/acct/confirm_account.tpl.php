<style type='text/css'>
#wrapper .err { color: red; }
.errfield { border: 2px solid red; }
#wrapper p {
  margin-top: 0.7em;
  margin-bottom: 0.7em;
}
</style>
<script type='text/javascript' src='acct_all.js'></script>
<div style='margin-top: 3.5em'>
<p>Confirm your account by entering your email address and password below.  This will fully enable your account 
and also generate customer support keys that will let you get help and 
assistance if you need it.</p>
<?if(isset($invalid)):?>
<p class='err'>One or more fields did not contain valid data.  Please check the highlighted fields and try again.</p>
<style type='text/css'>
<?foreach($invalid as $f):?>
  #<?=$f?> { border: 2px solid red; } 
<?endforeach;?>
</style>
<?endif;?>
<?if(isset($msg)):?>
<p class='err'><?=$msg?></p>
<?endif;?>
<form name='confirmationForm' method='POST'>
  <table>
    <tr><th>Email:</th><td><input name='email' id='email' type='text' value='<?=htmlentities($email)?>' size='50'/></td></tr>
    <tr><th>Password:</th><td><input name='pwd1' id='pwd1' type='password' value=''/></td></tr>
    <tr><th>Password (again):</th><td><input name='pwd2' id='pwd2' type='password' value=''/></td></tr>
    <tr><th>&nbsp;</th><td><input name='submit' type='submit' value='Submit'/></td></tr>
  </table>
</form>
</div>
