<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <link rel="stylesheet" href="admin.css" type="text/css"/>
    <title><?php echo $Secure_Url; ?> Edit User <?=$pretty_mcid?></title>
    <style type='text/css'><!--
      table th {
        text-align: left;
        vertical-align: top;
      }
// --></style>

  <body>
    <div id='header'>
      <h1><?php echo $Secure_Url; ?> User Administration</h1>
      <div id='user_tools'>
        Logged in as <b><?php echo $_SERVER['PHP_AUTH_USER']; ?></b>
      </div>
    </div>

    <div id="contents">
<!--
      <form method='get' action='user.php'>
        <label>User query:
          <input type='text' name='q' />
        </label>
        <input type='submit' value='Search' />
      </form>
-->

    <?php if (isset($msg)) { ?>
  <h2 style='color: green'><?php echo $msg; ?></h2>
           <?php } ?>

  <?php if (isset($mcid)) { ?>

      <h2>Account Info</h2>

  <form method='post' action='edit.php'>
    <input type='hidden' name='mcid' value='<?php echo $mcid; ?>' />

  <table>
    <tbody>
      <tr>
        <th>MCID:</th>
        <td><?php echo $pretty_mcid; ?></td>
      </tr>
      <tr>
       <th>Name:</th>
       <td><?php echo $name; ?></td>
      </tr>
      <tr>
	<th>Created:</th>
	<td><?php echo $since; ?></td>
      </tr>
      <tr>
	<th>CCR Log Updated:</th>
	<td><?php echo $ccrlogupdatetime; ?></td>
      </tr>
      <tr>
        <th>Email:</th>
        <td><a href='mailto:<?php echo $email; ?>'>
     <?php echo $email; ?></a><br />
        <span style="font-size: smaller; font-style: italic; color: gray;">(search for <a href='user.php?q=<?php echo $email; ?>'>
         other accounts</a> with this email)</span></td>
      </tr>
      <tr>
        <th>Groups:</th>
        <td><?php
foreach ($groups as $g) {
  echo $g['name'];

  if (isset($admin[(int) $g['groupinstanceid']]))
    echo '!';

  echo "<br />\n";
}
        ?></td>
      </tr>
      <tr>
        <th rowspan='2'>Password reset</th>
        <td>
          If the email address for this account is correct,
          you can reset the password to a random value,
          and email this random password to <?php echo $email ?>.<br />

           <input type='text' name='password' value='<?php echo $password; ?>' />

           <input type='submit' name='email' value='Send Email' />
  <br /><a href='edit.php?mcid=<?php echo $mcid; ?>'>new random password</a>
         </form>
        </td>
      </tr>
      <tr>
        <td>
   If the user has the <strong>MedCommons Account Receipt</strong>,
             then enter the next S/Key password here:<br />
        <input type='text' name='skey' />
        <input type='submit' name='verify' value='Verify User' />

        </td>
      </tr>

      <tr>
       <th>S/Key Recovery:</th>
       <td><a href='url.php?mcid=<?php echo $mcid; ?>'>emergency use only</a>
       </td>
      </tr>
    </tbody>
  </table>

  <?php } ?>
  </div>

  </body>
</html>
