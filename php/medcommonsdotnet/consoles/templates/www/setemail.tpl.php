{% extends "www/base.html" %}

{% block main %}

<div id='ContentBoxInterior'>
<h2>Change Email</h2>

<p>
Your email address will not be changed immediately.
</p>
<p>
We will send out a confirmation email.
Click on the link contained in that email
to complete the change.
</p>

<form method='post' action='setemail.php'>
  <div class='f'>
    <span class='n'>Current email</span>
    <span class='q'><?= $email ?></span>
  </div>
  <div class='f'>
    <span class='n'><label for='email'>New email</label></span>
    <span class='q'>
	<input name='email' value='<?= $new_email ?>' />
<?php
if (isset($error)) {
  echo "<div class='r errorAlert'>";
  echo $error;
  echo "</div>";
} ?>
    </span>
  </div>

  <div class='f'>
    <span class='n'>&nbsp;</span>
    <span class='q'>
      <input type='submit' class='primebutton' value='Change Email Address' />
    </span>
  </div>
</form>

{% endblock main %}

