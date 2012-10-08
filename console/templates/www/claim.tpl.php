{% extends "www/base.html" %}

{% block head %}

<script type="text/javascript" src='utils.js'></script>
<script type="text/javascript" src='ajlib.js'></script>

{% endblock head %}

{% block main %}

<div id='content'>
<h2>Claim Account</h2>

<form method='post' action='claim.php' id='password'>
  <fieldset>
    <legend>Change Password</legend>

    <p id='p_pw1'>
      <label>New password:
        <input class='infield'  type='password' name='pw1' id='pw1' />
      </label>

<?php
	if (isset($pw1_error)) {
?>
<div class='error'><?php echo $pw1_error; ?></div>
<?php
	}
?>
    </p>

    <p id='p_pw2'>
      <label>New password (again):
        <input class='infield'  type='password' name='pw2' id='pw2' />
      </label>

<?php
	if (isset($pw2_error)) {
?>
<div class='error'><?php echo $pw2_error; ?></div>
<?php
	}
?>
    </p>

<?php if (isset($next)) { ?>
    <input type='hidden' value='<?php echo $next; ?>' />
<?php } ?>

    <input type='submit' value='Change Password' />
  </fieldset>
</form>

</div>
 
{% endblock main %}

