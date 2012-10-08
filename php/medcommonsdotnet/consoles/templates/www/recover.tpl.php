{% extends "www/base.html" %}

{% block head %}

{% endblock head %}

{% block main %}

<div id='ContentBoxInterior'>
<h2>Change Password</h2>
<?php if (isset($error)) { ?>
<div class='error'><?php echo $error; ?></div>
<?php } ?>

<form method='post' action='recover.php?enc=<?= $enc ?>&hmac=<?= $hmac ?>' id='password' name='password'>

    <div class='f' id='p_pw1'>
      <span class='n'><label for='pw1'>New password:</label></span>
      <span class='q'>
        <input class='infield'  type='password' name='pw1' id='pw1' />

<?php
	if (isset($pw1_error)) {
?>
<div class='errorAlert r'><?php echo $pw1_error; ?></div>
<?php
	}
?>
      </span>
    </div>

    <div class='f' id='p_pw2'>
      <span class='n'><label for='pw2'>New password (again):</label></span>
      <span class='q'>
        <input class='infield'  type='password' name='pw2' id='pw2' />

<?php
	if (isset($pw2_error)) {
?>
<div class='errorAlert r'><?php echo $pw2_error; ?></div>
<?php
	}
?>
      </span>
    </div>

<?php if (isset($next)) { ?>
    <input type='hidden' value='<?php echo $next; ?>' />
<?php } ?>

    <div class='f'>
      <span class='n'>&nbsp;</span>
      <span class='q'>
    <input type='submit' class='primebutton'  value='Change Password' />
       </span>
    </div>
</form>
<script type="text/javascript">
document.password.pw1.focus();
</script>
</div>

{% endblock main %}

