{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}
<div id='forgot-password'>
    <div id="content">
    <h2>Password Recovery</h2>

<p>
There are two ways to recover your account.
</p>

<h3>1. Via Email</h3>
<p>
We can send password recovery instructors to
the email address we have on file
</p>

<form method='post' action='forgot.php' id='password' name='password'>
  <fieldset>
    <legend>Password Recovery</legend>

    <p id='p_mcid'>
      <label>Email
	<input class='infield' name='mcid' value='<?= $mcid ?>' id='mcid' />
      </label>
    </p>

<?php
if (isset($next)) {
?>
      <input type='hidden' value='<?= $next ?>' />
<?php
}
?>

    <input  type='submit' value='Send Email' />
  </fieldset>
</form><script type="text/javascript">
document.password.mcid.focus();
</script>

<p>
You will receive an email containing
instructions.
</p>
<p>
Follow these instructions to reset your password.
</p>
<hr />
<h3>2. Via Registration Receipt</h3>
<p>
You can recover your account if you have lost access to your email address,
but still have your <em>Registration Receipt</em>.
</p>
<p>
Your Registration Receipt was given when you first signed up
for an {{ ApplianceName }} account.
</p>
<p>
It looks something like this:
</p>
<pre>
<del>SAFE.SURE.TONE.JUDY.DIED.KEG</del>
MARK.TOTE.SICK.AID.RARE.AWK
</pre>
<form method='post' action='forgot.php'>
  <fieldset>
    <legend>Registration Receipt</legend>
<?php
if (isset($error)) {
?>
    <p class='error'><?= $error ?></p>
<?php
}
?>
    <label>MCID:<br />
      <input name='mcid' value='<?= $mcid ?>' />
    </label>
    <br />
    <label>Line:<br />
      <input name='skey' value='<?= $skey ?>' />
    </label>
<?php
if (isset($next)) {
?>
      <input type='hidden' value='<?= $next ?>' />
<?php
}
?>
    <br />
    <input type='submit' value='Log In' />
  </fieldset>
</form>

</div>
</div>
{% endblock main %}
