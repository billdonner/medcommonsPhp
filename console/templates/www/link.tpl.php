{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}
<div id = 'content'>
<h2>Link Accounts</h2>
<form method='post' action='auth.php' id='login'>
  <fieldset>
    <legend>Link User</legend>

{% if OnlineRegistration %}
    <p id='p_register'>
      <a href='register.php<?php if (isset($next)) echo '?next=' . $next;
           ?>' class='login'>register</a>
      &mdash; create a new MedCommons account
    </p>
{% endif %}

    <p id='p_openid'>
      <label>OpenID
	<input type='text' name='openid_url' id='openid_url' readonly='readonly'
	       size='30' value='<?= $openid ?>' />
      </label>
    </p>

    <p id='p_mcid'>
      <label>Email/MCID
        <input type='text' name='mcid' id='mcid'
               value='<?= $mcid ?>' />
       </label>

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
      <input type='password' name='password' id='password' />
    </label>
  </p>

<?php
if (isset($next)) {
?>
  <input type='hidden' name='next' value='<?php echo $next; ?>' />
<?php
}
?>
  <p id='p_loginb'>
  <input type='submit' value='Login' />
  <?if(isset($allow_anon_openid)):?>
  or <input type='submit' name="anon" value='Continue without Creating an Account' />
  <?endif?>
  </p>

  <p class='p_forgot'>
    <a href='forgot.php<?php if (isset($next)) echo '?next=' . $next; ?>'
       class='login'>
      forgot password
    </a> &mdash; sends a new password to your registered email address
  </p>


  </fieldset>

  
</form>
</div>
{% endblock main %}
