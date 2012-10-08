<?php
//baseline HomePage on MedCommons Appliance (must always log in)
//  -- this is the essence of index.php
//  -- the cookie is set by logging on
//  -- the single css file can be modified or replaced to change layout
//  -- the other variables are preloaded on the way in from the medcommons operator console
$cookie= (isset($_COOKIE['mc']));
$xyz = $GLOBALS['Accounts_Url'];
//
// the stamp reveals the user identity if logged on
// the stamp can be clicked on to logon or logoff
if (!$cookie) $aimg = "<!-- not logged on -->"; else 
$aimg = "<!--logged on --><a href='/identity/logout?next=/acct/index.php'><img id='stamp' src='stamp.php' alt='onoffstamp' /></a>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head><title>HomePage for&nbsp;<?=$acApplianceName?> on <?=$acDomain?></title></head>
      <link rel="stylesheet" type="text/css" href="<?=$acStyleSheet?>"/>
<body>
<div id='wrapper'>
 <div id='header'> <!-- appliance specific info set by console -->
  <div id='branding'>
   <a href='<?=$acHomePage?>'><img id='logo' src='<?=$acLogo?>' alt='<?=$acAlt?>' /></a>
   </div>
   <div id='volatile'>
   <?=$acApplianceName?> on <?=$acDomain?><br/>
   <?=$acHomePage?><br/>
   <?=$acOwner?><br/>
  </div>
  </div><!-- ends header -->
 <? if ($cookie) { ?>
   <div class="stampclick"><?=$aimg?></div>
<? }?>
   <div id='headline'><?=$acMessage?></div>
  <? if ($cookie) { ?>
    <br/>
 <div id='nav'><ul>links
<li><a href="/identity/logout?next=/acct/index.php">Logout</a></li>
<li><a href='<?=$GLOBALS['Commons_Url']?>gwredir.php?a=CreateCCR'>CreateCCR</a></li>
<li><a href='<?=$GLOBALS['Commons_Url']?>gwredir.php?a=ImportCCR'>ImportCCR</a></li>
<li><a href='groupdemo/'>DoctorDemo</a></li>
<li><a href='patientdemo/'>PatientDemo</a></li>
</ul>
</div>
<? }?>
<? if (!$cookie) { ?>
<br/>
<form method='post' action='login.php' id='login'>
  <table><tr><td width='300px' align='right'>
  <label for='aaa'>Please Login to <?=$acApplianceName?></label></td>
<td width='300px' align='left'>
    <?php if ($acOnlineRegistration) { ?>
      <a href='register.php?next=index.php'
         title='create a new MedCommons account'
         class='login'>register</a>
    <?php } ?>
 </td></tr>  
<tr><td width='300px' align='right'>
      <label for='mcid'>Email or MCID</label></td>
      <td width='300px' align='left'>
        <input type='text' name='mcid' id='mcid' /></td></tr>
     <tr><td width='300px' align='right'>
      <label for='password'>Password</label></td>
      <td width='300px' align='left'>
       <input type='password' name='password' id='password' /></td></tr>    
    <input type='hidden' name='next' value='index.php' />
  <tr><td width='300px' align='right'> <input type='submit' value='Login' />
  </td>
  <td width='300px' align='left'>
    <?php if ($acOnlineRegistration) { ?>
    <a href='forgot.php?next=index.php'
		title = 'sends a new password to your registered email address'
       class='login'>
      forgot password
    </a>
      <?php } ?>
    </td></tr>
    </table>
  </form>
<? } ?>
<div id='content'>
<? if ($cookie) { ?>
 <hr/> <h5>HIPAA Records Request / Consent Form</h5>
  <form name="coverForm" action="cover.php?createCover=true" method="post" target="_new">
    <table>
    <tr><td width='300px' align='right'>
      <label for='accid'>PHR Account Number</label></td>
      <td width='300px' align='left'>
        <input type='text' name='accid' id="hippaPatientId"  size="20" /></td></tr>
     <tr><td width='300px' align='right'>
      <label for='coverProviderCode'>Provider/Practice</label></td>
      <td width='300px' align='left'>
       <input type='text' name='coverProviderCode' id='coverProviderCode' /></td></tr>
           <tr><td width='300px' align='right'>
      <label for='coverNotifyEmail'>Notification (opt)</label></td>
      <td width='300px' align='left'>
        <input type='text' name='coverNotifyEmail' id="coverNotifyEmail"  size="20" /></td></tr>
     <tr><td width='300px' align='right'>
      <label for="coverPin">PIN (opt)</label></td>
      <td width='300px' align='left'>
       <input type='text' name="coverPin" id="coverPin" /></td></tr>
       </table>
       <input type="submit" value="Preview"/>
  </form>
      <hr/><h5>Small CCR Gadget (iframe)</h5>
   <p>   
<iframe src='CCCRGadget.php' 
width='100%' background-color='transparent' allowtransparency
='true'  frameborder='0' scrolling='no' height='100'> Your browser does not support iframes.</iframe>
    </p>  
<hr/><h5>Worklist (iframe)</h5>
<p>
      <iframe src='myworklist/?tpl=widget' 
width='100%' background-color='transparent' allowtransparency
='true'  frameborder='0' scrolling=
'no' height='300'> Your browser does not support iframes.</iframe>
      </p>
<? } ?>
</div>
<div id='footer'>(c) 2007 <a href='http://www.medcommons.net/' >MedCommons, Inc.</a>
</div>
</body>
</html>