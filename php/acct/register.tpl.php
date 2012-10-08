
<style type='text/css'>
  fieldset {
    border: 1px solid #333;
    padding: 1em;
  }
  #registerContents {
    background-color: #F2F0E5;
    border: 1px solid #444;
    padding: 1em 3em 1em 1em;
    margin-left: 0.8em;
  }
  label {
    font-weight: normal;
  }
  p {
    margin: 0.5em 0px;
  }
  #registerContents table tr th {
    text-align: left;
  }
  #registerContents table tr td {
    padding-top: 0.4em;
  }
  #registerContents table {
    border-spacing: 2em 0.4em;
  }
  td input {
    width: 13em;
  }
  td.error,div.error {
    color: red;
  }
  #main h2 {
   margin-top: 0.2em;
   margin-bottom: 0.5em;
   padding-bottom: 0px;
  }

  #tablewrapper {
    max-width: 700px;
    clear: both;
  }

  table.tinst {
    margin: 30px 10px 0px 0px;
  }
  table.tinst td, table.tinst th {
    padding: 10px;
  }
</style>


<div id="">

<h2>Register <?if(isset($reg_group)):?>to Join Group <?=htmlentities($reg_group->name)?><?endif;?></h2>

<?if(isset($_GET['dod'])):?>
<div style='border: solid 1px black; background-color: #ffffee; padding: 10px; margin: 10px 0px; max-width: 600px;'>
    <strong>Note:</strong> Dicom On Demand is a trial service and provides only <strong>temporary</strong> 
    storage of imaging data.  Please see the <a href='http://www.medcommons.net/termsofuse.php'>Terms of Use</a> 
    for more information.
</div>
<?endif;?>

Registering with MedCommons is a three step process:
<ol class='normal'>
  <li>
    Complete this <strong>registration form</strong>,
    telling MedCommons about yourself.
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

<br/>
<form method='post' action='register.php' id='login' name='login'>
  <div class='left'>
<div id='registerContents'>

<?php
  if (isset($next)) {
?>
    <input type='hidden' name='next' value='<?php echo $next; ?>' />
<?php
}
?>
<?if(isset($activationKey)):?>
    <input type='hidden' name='ActivationKey' value='<?=htmlentities($activationKey)?>'/>
<?endif;?>
<?if(isset($_GET['dod'])):?>
    <input type='hidden' name='dod' value='true'/>
<?endif;?>
    <table border='0'>
     <tr>
      <th>
       <label for='fn'>First Name</label>
      </th>
      <th>
       <label for='ln'>Last Name</label>
      </th>
     </tr>
     <tr>
      <td>
       <input class='infield'  type='text' name='fn' id='fn' value='<?= $fn ?>' />
      </td>
      <td>
       <input class='infield'  type='text' name='ln' id='ln'
              value='<?= $ln ?>' />
      </td>
      <td>&nbsp;</td>
     </tr>
     <tr id='p_email'>
      <th><label>Email</label></th>
      <td><input class='infield'  type='text' name='email' id='email'
        <?if(isset($fixedEmail)):?>
          readonly="true"  style="color: #888; background-color: #f6f6f6;"
        <?endif;?>
               value='<?= $email ?>' />
       </td>
       <td class='error'>
<?if(isset($email_error)):?>
      <?php echo $email_error; ?>
<?endif;?>
      </td>
     </tr>
     <tr id='p_pw1'>
        <th>
           <label>Password</label>
        </th>
        <td>
          <input class='infield'  type='password' name='pw1' id='pw1' />
        </td>
        <td class='error'> 
          <?if (isset($pw1_error)):?> <?php echo $pw1_error; ?> <?endif;?>
        </td>
      </tr>
      <tr>
        <th><label>Password (again)</label></th>
        <td><input class='infield'  type='password' name='pw2' id='pw2' /></td>
        <td class='error'> <?if(isset($pw2_error)):?> <?php echo $pw2_error; ?> <?endif;?></td>
      </tr>
      </table>
      <div style='margin-left: 2em;'>

      <p id='p_termsOfUse'>
      <label>
        <input class='infield'  type='checkbox' name='termsOfUse' id='termsOfUse' />
        I have read and understand the
        <a target='_new' href="/termsofuse.php">
          Terms Of Use
        </a>
      </label>
      <?php
if (isset($tou_error)) {
?>
      <div class='error'>
        <?php echo $tou_error; ?>
      </div>
<?php
}
?>
    </p>

<br/>
    <input  type='submit' value='Register' />
    </div>
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
<div id='tablewrapper'>
<table class='tinst' style=''>
  <tbody>
    <tr>
      <th>Why Register?</th>
      <td>
        Paid subscribers can upload CCR, PDF, Fax and diagnostic imaging files
        to a HealthURL account that they control. Access to paid accounts is controlled through Facebook, OpenID,
        verified email addresses and other controlled sharing features. 
      </td>
    </tr>
  </tbody>
</table>
</div>

