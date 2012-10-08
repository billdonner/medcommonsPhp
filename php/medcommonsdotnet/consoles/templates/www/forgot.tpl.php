{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}
<div id='ContentBoxInterior'>
    <h2>Password Recovery</h2>

<p>
There are two ways to recover your account.
</p>

<h3>1. Via Email</h3>
<p>
We can send password recovery instructions to
the email address we have on file
</p>

<form method='post' action='forgot.php' id='password' name='password'>
    <div class='f' id='p_mcid'>
      <span class='n'><label for='mcid'>Email</label></span>
      <span class='q'>
	<input class='infield' name='mcid' value='<?= $mcid ?>' id='mcid' />
      </span>
    </div>

<?php
if (isset($next)) {
?>
      <input type='hidden' value='<?= $next ?>' />
<?php
}
?>

    <div class='f'>
      <span class='n'>&nbsp;</span>
      <span class='q'>
    <input  type='submit' value='Send Email' />
      </span>
    </div>
</form><script type="text/javascript">
document.password.mcid.focus();
</script>
<br />
<br />
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
<pre id='skey' style='padding-left: 50px;'>
<del>SAFE.SURE.TONE.JUDY.DIED.KEG</del>
MARK.TOTE.SICK.AID.RARE.AWK
</pre>
<form method='post' action='forgot.php'>
   <div class='f'>
      <span class='n'><label for='mcid'>MCID:</label></span>
      <span class='q'>
      <input name='mcid' value='<?= $mcid ?>' />
<?php
if (isset($error)) {
?>
    <p class='errorAlert r'><?= $error ?></p>
<?php
}
?>
       </span>
    </div>
    <div class='f'>
      <span class='n'><label for='skey'>Line:</label></span>
      <span class='q'>
      <input name='skey' value='<?= $skey ?>' />
      </span>
    </div>
<?php
if (isset($next)) {
?>
      <input type='hidden' value='<?= $next ?>' />
<?php
}
?>
    <div class='f'>
      <span class='n'>&nbsp;</span>
      <span class='q'>
    <input type='submit' class='primebutton' value='Log In' />
      </span>
    </div>
</form>
<br />
<br />
</div>
{% endblock main %}
