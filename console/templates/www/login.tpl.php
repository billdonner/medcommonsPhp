{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}
<div id='ContentBoxInterior'>
<h2>Sign In</h2>
<form class='p' method='post' action='login.php' id='login' name='login'>


{% if OnlineRegistration %}
    <p id='p_register'>
      No account?
      <a href="register.php<?php if (isset($next)) echo '?next=' . $next;
           ?>" class='login'>Register</a>
      to create a new MedCommons account
    </p>
{% endif %}
<?php $name = (isset($password) && $password) ? 'mcid' : 'openid_url'; ?>
  <div class='f' id='p_openid_url'>
    <label class='n' for='<?= $name ?>'>&nbsp;</label>
    <span class='q'>
      <input class='infield' type='text' name='<?= $name ?>' id='<?= $name ?>'
             size='30' value="<?= $openid_url ?>" />
      <div class='r'>
<?php if (isset($error)) { ?>
  <div class='errorAlert'>
    <?= $error ?>
  </div>
<?php } ?>
        <em>
          <div>http://user.openid.com</div>
          <div>user@email.com</div>
          <div>9235-1234-5678-9012</div>
        </em>
      </div>
    </span>
  </div>

<?php if ($password) { ?>
  <div class='f' id='p_password'>
    <label class='n' for='password'>Password</label>
    <div class='q'>
    <input class='infield' type='password' name='password' id='password'
     size='30' 
     <?if(isset($demo_password)):?>value='<?=$demo_password?>'<?endif;?>
      />
    </div>
  </div>

<?php
}
if (isset($next)) {
?>
  <input    type='hidden' name='next' value="<?= $next ?>" />
<?php
}
?>

  <div class='f'>
    <span class='n'>&nbsp;</span>
    <div class='q'>
    <input type='submit' value='Sign In' name='loginsubmit'/>
    </div>
  </div>

<?if(!isset($demo_password)):?>
  <div class='f' id='p_forgot'>
    <span class='n'>&nbsp;</span>
    <span class='q'>
    <a href="forgot.php<?php if (isset($next)) echo '?next=' . $next; ?>"
       class='login'>
      Problems?
    </a>
    </span>
  </div>
<?else:?>
<div id='demomsg'>
  <p>Because you are accessing a demo account, the username and password 
     have been pre-populated for you. To continue, just press "Sign In"!</p>
</div>
<?endif;?>
  
</form>
<?php if ($password) { ?>
<script type="text/javascript">
<?if(!isset($demo_password)):?>
  document.login.password.focus();
<?else:?>
  document.login.loginsubmit.focus();
<?endif;?>
</script>
<?php } ?>

<br />
<br />
<br />
</div>
{% endblock main %}
