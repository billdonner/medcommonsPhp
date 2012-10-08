<?
require_once "urls.inc.php";
/**
 * Log the user in to Jane Hernandez's group
 */
//header("Location: ".$GLOBALS['Identity_Base_Url']."/login?mcid=jhernandez@medcommons.net&password=tester");
?>
<html style='font-family: arial;'>
<body onload='document.forms[0].submit();'>
  <p>Creating Demonstration CCR ...</p>
  <form method='post' action='<?=$GLOBALS['Default_Repository']."/tracking.jsp"?>'>
    <input type="hidden" name="tracking" value="new"/>
  </form>
</body>
</html>
