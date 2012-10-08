{% extends "www/base.html" %}

{% block main %}
<div id='ContentBoxInterior'>
<h2>Register</h2>
<p>
Registering with MedCommons is a three step process:
</p>

<ol>
  <li>
    Complete this <strong>registration form</strong>,
    telling MedCommons about yourself;
  </li>
  <li>
    MedCommons will send you a <strong>confirmation email</strong>;
  </li>
  <li>
    The confirmation email will contain a link to your
    MedCommons <strong>receipt</strong>, which you must print out
    for your records.
  </li>
</ol>

<form method='post' action='register.php' id='login' name='login' class='p'>

<?php
  if (isset($next)) {
?>
    <input type='hidden' name='next' value="<?= $next ?>" />
<?php
}
?>
<?if(isset($activationKey)):?>
    <input type='hidden' name='activationKey' value="<?=htmlentities($activationKey)?>" />
<?endif;?>
  <div class='f'>
    <label for='fn' class='n'>First Name</label>
    <div class='q'>
       <input class='infield'  type='text' name='fn' id='fn'
              value="<?= $fn ?>" />
    </div>
  </div>
  <div class='f'>
    <label for='ln' class='n'>Last Name</label>
    <div class='q'>
       <input class='infield' type='text' name='ln' id='ln'
              value="<?= $ln ?>" />
    </div>
  </div>
  <div class='f' id='p_email'>
    <label for='email' class='n'>*Email</label>
    <div class='q'>
      <input class='infield'  type='text' name='email' id='email'
        <?if(isset($fixedEmail)):?>
          readonly="true"  style="color: #888; background-color: #f6f6f6;"
        <?endif;?>
               value="<?= $email ?>" />
<?php
if (isset($email_error)) {
?>
      <span class='errorAlert r'>
        <?= $email_error ?>
      </div>
<?php
}
?>
    </div>

    <div class='f' id='p_pw1'>
      <label for='pw1' class='n'>*Password</label>
      <div class='q'>
        <input class='infield'  type='password' name='pw1' id='pw1' />
<?php
if (isset($pw1_error)) {
?>
      <span class='errorAlert r'>
        <?= $pw1_error ?>
      </span>
<?php
}
?>
      </div>
    </div>

    <div class='f' id='p_pw2'>
      <label for='pw2' class='n'>*Password (again)</label>
      <div class='q'>
        <input class='infield'  type='password' name='pw2' id='pw2' />
<?php
if (isset($pw2_error)) {
?>
      <span class='errorAlert r'>
        <?= $pw2_error ?>
      </span>
<?php
}
?>
      </div>
    </div>

    <div class='f' id='p_termsOfUse'>
      <label for='termsOfUse' class='n'>
        I have read and understand the
        <a target='_new' href="/termsofuse.php">
          Terms Of Use
        </a>
      </label>
      <div class='q'>
        <input class='infield' type='checkbox' name='termsOfUse' id='termsOfUse' />
<?php if (isset($tou_error)) { ?>
        <div class='errorAlert r'><?= $tou_error ?></div>
<?php } ?>
      </div>
    </div>

    <div class='f'>
      <span class='n'>&nbsp;</span>
      <div class='q'>
        <input  type='submit' class='primebutton' value='Register' />
      </div>
    </div>
</form>
<script type="text/javascript">
document.login.fn.focus();
</script>
<?php

if (isset($db_error)) {
  echo "<p class='error'>";
  echo $db_error;
  echo "</p>";
}

?>
</div>
</div>

{% endblock main %}

