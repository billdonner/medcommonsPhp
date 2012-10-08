{% extends "www/base.html" %}

{% block main %}

<div id='ContentBoxInterior'>
<h2>Change Name</h2>

<form method='post' action='edituser.php'>
  <div class='f'>
    <span class='n'>Current name</span>
    <span class='q'><?= $first_name ?> <?= $last_name ?></span>
  </div>

  <div class='f'>
    <span class='n'>New name</span>
    <span class='q'>
	  <input name='first_name' value='<?= $first_name ?>' />
	  <input name='last_name' value='<?= $last_name ?>' />
<?php
if (isset($error)) {
  echo "<div class='errorAlert r'>";
  echo $error;
  echo "</div>";
} ?>
    </span>
  </div>

  <div class='f'>
    <span class='n'>&nbsp;</span>
    <span class='q'>
	<input type='submit' class='primebutton' value='Change Name' />
    </span>
  </div>
</form>

{% endblock main %}

