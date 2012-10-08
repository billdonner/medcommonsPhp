{% extends "www/base.html" %}

{% block main %}

<div id='content'>
<h2>Change Name</h2>

<form method='post' action='edituser.php'>
  <table>
    <tbody>
      <tr>
	<th>Current Name:</th>
	<td><?= $first_name ?> <?= $last_name ?></td>
      </tr>
      <tr>
	<th>New name:</th>
	<td>
	  <input name='first_name' value='<?= $first_name ?>' />
	  <input name='last_name' value='<?= $last_name ?>' />
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
	<td><input type='submit' value='Change Name' /></td>
      </tr>
    </tbody>
  </table>
</form>

{% endblock main %}

