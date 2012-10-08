{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}
<div id='ContentBoxInterior'>
<h2>Link Accounts</h2>
<form method='post' action='auth.php' id='login'>

{% if OnlineRegistration %}
    <div class='f' id='p_register'>
      <span class='n'>&nbsp;</span>
      <span class='q'>
      <a href='register.php<?php if (isset($next)) echo '?next=' . $next;
           ?>' class='login'>register</a>
      &mdash; create a new MedCommons account
      </span>
    </div>
{% endif %}

    <div class='f' id='p_openid'>
      <span class='n'><label for='openid_url'>OpenID</label></span>
      <span class='q'>
	<input type='text' name='openid_url' id='openid_url' readonly='readonly'
	       size='30' value='<?= $openid ?>' />
      </span>
    </div>

    <div class='f' id='p_mcid'>
      <span class='n'><label for='mcid'>Email/MCID</label></span>
      <span class='q'>
        <input type='text' name='mcid' id='mcid'
               value='<?= $mcid ?>' />
<?php
if (isset($error)) {
?>
  <div class='errorAlert r'>
    <?php echo $error; ?>
  </div>
<?php
}
?>
      </span>
    </div>

  <div class='f' id='p_password'>
    <span class='n'><label for='password'>Password</label></span>
    <span class='q'>
      <input type='password' name='password' id='password' />
    </span>
  </div>

<?php
if (isset($next)) {
?>
  <input type='hidden' name='next' value='<?php echo $next; ?>' />
<?php
}
?>
  <div class='f' id='p_loginb'>
    <span class='n'>&nbsp;</span>
    <span class='q'>
  <input type='submit' value='Sign In' />
  <?if(isset($allow_anon_openid)):?>
  or <input type='submit' name="anon" value='Continue without Creating an Account' />
  <?endif?>
    </span>
  </div>

  <div class='f' id='p_forgot'>
    <span class='n'>&nbsp;</span>
    <span class='q'>
    <a href='forgot.php<?php if (isset($next)) echo '?next=' . $next; ?>'
       class='login'>
      Problems?
    </a>
    </span>
  </div>


  
</form>
<br />
<br />
<br />
</div>
{% endblock main %}
