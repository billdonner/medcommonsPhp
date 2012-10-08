{% extends "www/base.html" %}

{% block main %}

<?include "urls.inc.php"?>
<script type="text/javascript" src='{{ Site }}/acct/mini-mochi.js'></script>
<script type="text/javascript" src='{{ Site }}/acct/utils.js'></script>
<?if(isset($msg)):?>
<p id='loginMsg'><?=$msg?></p>
<br/>
<?endif;?>

<?$formStyle = "";?>
<?if(isset($autoLoginId)):?>
  <p>Automatically logging in to demonstration account ...</p>
 <?$formStyle = 'display: none;';?>
<?else:?>
  <p style='margin-top: 50px;'>Please login to access this Health Record:</p>
<?endif;?>
<br/>
<form name='loginForm' style="<?=$formStyle?>" method='post' action='<?=$Secure_Url?>/acct/login.php' id='login'>
  <?php if (isset($next)) { ?>
    <input type='hidden' name='next' value='<?= $next ?>' />
  <?php } ?>
  <?php
  if (isset($allow_anon_openid)) {
  ?>
    <script type="text/javascript">addLoadEvent(function() { if($('openidOtherProviders')) removeElementClass($('openidOtherProviders'),'invisible'); });</script>
    <input type='hidden' name='allow_anon_openid' value='<?=$allow_anon_openid; ?>' />
  <?php
  }
  ?>
  <?php if (isset($autoLoginId)) { ?>
    <input type='hidden' name='password' value='tester' />
  <?php } ?>

  <table>
    <tbody>
{% include "www/login.inc" %}
    <?php if (isset($error)) { ?>
      <tr>
        <td colspan='2'>
            <div class='error'><?= $error; ?></div>
        </td>
      </tr>
    <?php } ?>

 <tr>
  <td></td>
  <td>
      <a href='forgot.php<?php if (isset($next)) echo '?next=' . $next; ?>'
         class='login' title='sends a new password to your registered email address'>
        forgot password?
      </a> 
    </td>
  </tr>
    </tbody>
  </table>
</form>

<?php if (isset($autoLoginId)) { ?>
  <script type="text/javascript"> addLoadEvent(function() { with(document.loginForm) { openid_url.value = '<?=$autoLoginId?>'; submit(); }});</script>
<?php } ?>
 {% endblock main %}

