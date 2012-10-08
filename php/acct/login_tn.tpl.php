<?include "urls.inc.php"?>
<script type="text/javascript" src='utils.js'></script>
<script type="text/javascript">
  function track() {
    document.loginForm.action="<?=gpath('Commons_Url').'/trackemail.php'?>";
    document.loginForm.submit();
  }
</script>
<?if(isset($msg)):?>
<p id='loginMsg'><?=$msg?></p>
<br/>
<?endif;?>

<?$formStyle = "";?>
<?if(isset($autoLoginId)):?>
  <p>Automatically logging in to demonstration account ...</p>
  <script type="text/javascript">
    window.onload = function() { with(document.loginForm) { mcid.value = '<?=$autoLoginId?>'; password.value = 'tester'; submit(); }};
  </script>
  <?$formStyle = 'display: none;';?>
<?else:?>
  <p>Please login or provide a valid Tracking Number and PIN to access this Health Record:</p>
<?endif;?>
<br/>
<form name='loginForm' style="<?=$formStyle?>" method='post' action='<?=$Secure_Url?>/acct/login.php' id='login'>
    <table>
      <tr>
        <th id="emailId">Email/Account ID:</th>
        <td id="emailId"><input type='text' name='mcid' id='mcid' tabindex='1' value='' /></td>
        <td id="loginOr" rowspan="4" valign="center" align="center"  width="60em">or</td>
        <th id="emailId">Tracking Number:</th>
        <td id="emailId"><input type='text' name='a' id='tn' tabindex='4' value='' /></td>
      </tr>
      <tr>
        <td colspan='2'>
          <?php
          if (isset($error)) {
          ?>
            <div class='error'>
              <?php echo $error; ?>
            </div>
          <?php
          }
          ?>
        </td>
      </tr>
      <tr>
        <th class="t_password">Password:</th>
        <td class="t_password"><input type='password' name='password' tabindex='2' id='password' /></td>
        <th class="t_password">PIN:</th>
        <td class="t_password"><input type='password' name='p' id='pin' tabindex='5' /></td>
      </tr>

  <?php
  if (isset($next)) {
  ?>
    <input type='hidden' name='next' value='<?php echo $next; ?>' />
  <?php
  }
  ?>
  <tr>
    <th class='t_login'>&nbsp;</th><td class="t_track"><input type='submit' tabindex='3' value='Login'/>
    &nbsp;
      <a href='forgot.php<?php if (isset($next)) echo '?next=' . $next; ?>'
         class='login' title='sends a new password to your registered email address'>
        forgot password?
      </a> 
    </td>
    <th class='t_track'>&nbsp;</th><td class="t_track"><button onclick='track();' tabindex='6'>Track</button></td>
  </tr>
  </table>
  <br/>
  <br/>
</form>
