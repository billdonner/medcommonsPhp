{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}
<div id = 'content'>
<h2>Please Sign In</h2>
<form method='post' action='<?= $loginUrl ?>' id='login' name='login'>

  <fieldset>
    <legend>Log in</legend>

<?php if (isset($errors) && $errors) { ?>
<ul class='error'>
<?php foreach ($errors as $error) { ?>
  <li><?= $error ?></li>
<? } ?>
</ul>
<? } ?>
    <p id='p_mcid'>
      <label>MCID
        <input class='infield'   type='text' name='mcid' id='mcid'
               value='<?= $mcid ?>' />
       </label><br />

  </p>

  <p id='p_password'>
    <label>Password
      <input  class='infield'  type='password' name='password' id='password' />
    </label>
  </p>

  <input  type='submit' value='Login' />

  <p class='p_forgot'>
    <a href='forgot.php<?php if (isset($next)) echo '?next=' . $next; ?>'
       class='login'>
      forgot password
    </a> &mdash; sends a new password to your registered email address
  </p>

  </fieldset>

  
</form>
<?php if ($mcid) { ?>
<script type="text/javascript">
document.login.password.focus();
</script>
<?php } ?>

</div>
{% endblock main %}
