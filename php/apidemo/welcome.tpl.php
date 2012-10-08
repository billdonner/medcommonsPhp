<h2>Welcome to the Cancer Risk Calculator!</h2>
<?if(isset($error)):?>
<p style='color: red'>A problem occurred executing your request!</p>
<p><?=$error?></p>
<?else:?>
<p>This service will help you understand your cancer risk by analyzing your medical record
and matching it with the latest medical knowledge to estimate your personal risk 
factor for cancer.</p>

<p>In order to calculate your cancer risk, we need you to authorize us access to your Health URL.
Clicking the Authorize button below will take you to the login page of your Appliance where you
can enter your login information to authorize our access.</p>
<?endif;?>

<form name='accountForm' method='POST'>
  <table>
    <tr><td>Enter your MedCommons Account Id:</td><td><input name='mcid' type='text' size='16' maxlength='16'/></td></tr>
    <tr><td>&nbsp;</td><td><input type='submit' name='calculate' value='Authorize'/></td></tr>
  </table>
</form>
