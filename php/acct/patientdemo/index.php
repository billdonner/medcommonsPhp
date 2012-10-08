<?
require_once "urls.inc.php";
/**
 * Log the user in to Jane Hernandez's group 
 */
//header("Location: ".$GLOBALS['Identity_Base_Url']."/login?mcid=jhernandez@medcommons.net&password=tester");
?>
<html style='font-family: arial;'>
<body onload='document.forms[0].submit();'>
  <p>Logging in to Demonstration Account ...</p>
  <form method='post' action='<?=rtrim($GLOBALS['Accounts_Url'],"/")?>/login.php'>
    <input type="hidden" name="mcid" value="jhernandez@medcommons.net"/>
    <input type="hidden" name="password" value="tester"/>
    <input type='hidden' name='next' value='<?=rtrim($GLOBALS['Accounts_Url'],"/")?>/goStart.php' />  
</form>
</body>
</html>
