{% extends "www/base.html" %}

{% block main %}

<div id='content'>
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
  <table>
    <tbody>
      <tr>
	<th>Current Email Address:</th>
	<td><?= $email ?></td>
      </tr>
      <tr>
	<th>New Email Address:</th>
	<td>
	  <input name='email' value='<?= $new_email ?>' />
<?php
if (isset($error)) {
  echo "<div class='error'>";
  echo $error;
  echo "</div>";
} ?>
	</td>
      </tr>
      <tr>
	<td></td>
	<td><input type='submit' value='Change Email Address' /></td>
      </tr>
    </tbody>
  </table>
</form>

{% endblock main %}

