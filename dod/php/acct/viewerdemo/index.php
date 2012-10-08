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
  <form method='post' action='<?=$GLOBALS['Identity_Base_Url']."/login"?>'>
    <input type="hidden" name="mcid" value="demodoctor@medcommons.net"/>
    <input type="hidden" name="password" value="tester"/>
    <input type="hidden" name="next" value="<?=$GLOBALS['Commons_Url']?>gwredirguid.php?guid=277be17bfed0d27caa7ba9a9513a4293572f6617&mode=view"/>
  </form>
</body>
</html>
