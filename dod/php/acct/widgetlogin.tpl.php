<?
require_once "urls.inc.php";
$irl = $GLOBALS['Identity_Base_Url'];
?>
<h4>Existing Account</h4>
<div id='login'>
<p>If you have an existing account you may login to access your Worklist.</p>
<form method='post' action='<?=$irl?>/login?next=<?=$next?>'>
  <table><tr><td align="right">Your MCID or E-Mail Address:&nbsp;</td><td><input name='mcid' size='19' value='' /></tr>
        <tr><td align="right">Your Password:&nbsp;</td><td><input name='password' type='password'/></td></tr>
        <tr><td>&nbsp;</td><td align="right"><input type='submit' value='Sign On>>' /></td></tr>
    </tr>
  </table>
</form>
</div>
</p>

