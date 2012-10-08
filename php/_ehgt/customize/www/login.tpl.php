{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}
<div id = 'content'>
<h2>Please Sign In</h2>
<form method='post' action='login.php' id='login' name='login'>

  <fieldset>
    <legend>Log in</legend>

{% if OnlineRegistration %}
    <p id='p_register'>
      No account?
      <a href='register.php<?php if (isset($next)) echo '?next=' . $next;
           ?>' class='login'>Register</a>
      to create a new MedCommons account
    </p>
{% endif %}


    <p id='p_mcid'>
      <label>Email or Account ID
        <input class='infield'   type='text' name='mcid' id='mcid'
               value='<?= $mcid ?>' />
       </label><br />

<?php
if (isset($error)) {
?>
  <div class='error'>
    <?php echo $error; ?>
  </div>
<?php
}
?>
  </p>

  <p id='p_password'>
    <label>Password
      <input  class='infield'  type='password' name='password' id='password' />
    </label>
  </p>

<?php
if (isset($next)) {
?>
  <input    type='hidden' name='next' value='<?php echo $next; ?>' />
<?php
}
?>

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
