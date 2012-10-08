{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}

<div id='content'>

<?php if (isset($error)) { ?>
<div class='error'><?php echo $error; ?></div>
<?php } ?>

<form method='post' action='recover.php?enc=<?= $enc ?>&hmac=<?= $hmac ?>' id='password' name='password'>
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
<script type="text/javascript">
document.password.pw1.focus();
</script>
</div>

{% endblock main %}

