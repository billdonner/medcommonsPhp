<?
require_once "urls.inc.php";

?>
<html style='font-family: arial;'>
<body onload='document.forms[0].submit();'>
  <p>Logging in to Demonstration Account ...</p>
  <form method='post' action='<?=rtrim($GLOBALS['Accounts_Url'],"/")?>/login.php'>
    <input type="hidden" name="mcid" value="demodoctor@medcommons.net"/>
    <input type="hidden" name="password" value="tester"/>
    <input type="hidden" name="next" value="<?=rtrim($GLOBALS['Accounts_Url'],"/")."/home.php?expand=worklist"?>"/>
  </form>
</body>
</html>
