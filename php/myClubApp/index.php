<?php
/* 
 * This is a very basic top level sign on screen - modify as desired
 * a cookie is setup to hold the user's openid, and is used to test 
 * to see whether logged on 
 *
 * Copyright 2008 MedCommons Inc.
 */
if(isset($_REQUEST['err'])) 
  $err=$_REQUEST['err']; 
else 
  $err='';

// If this is a logout, redirect to back with appropriate message
if(isset($_REQUEST['logout'])) {
  setcookie('u',false); 
  header("Location: index.php?err=youweresignedout$err"); 
  exit;
}

// If logged in, go to main page
if(isset($_COOKIE['u'])) {
  header("Location: main.php"); 
  exit;
}

// Display main page
$markup = <<<XXX
<h3>Please Sign On</h3>
<p>To use this service, you must register with Your Generic Club and have a valid OpenId.  
For more information contact your club officers</p>
<form method='post' action='auth.php'>
<table>
  <tbody>
    <tr>
      <td class='prompt'><label for='openid_url'>User Name</label></td>
      <td><input class='infield' type='text' value='' id='openid_url' name='openid_url' /></td>
      <td class='errfield'>$err</td>
    </tr>
    <tr>
      <td></td>
      <td><input type='submit' value='Sign On'></td>
      <td><img src="images/openid.jpg" width='38' height='35' alt='OpenID' /></td>
    </tr>
  </tbody>
</table>
</form>
</div>
</body>
XXX;

echo $markup;
?>
