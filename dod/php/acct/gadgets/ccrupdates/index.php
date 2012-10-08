<?
/**
 * Current CCR Updates Gadget
 * 
 * Renders the updates that have occurred to a user's current CCR. Actually
 * the information is rendered by the gateway that is storing the current ccr.
 * This page simply redirects the user there.
 */
require_once "../../alib.inc.php";
aconnect_db();
$info = testif_logged_in();
if($info) {
  $guid = getCurrentCCRGuid($info[0]);
  if($guid) {
  	header("Location: ".$GLOBALS['Commons_Url']."/gwredirguid.php?guid=$guid&dest=access?displayUpdates");
  	exit;
  }
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Current CCR Updates</title>
	<link href="../../main.css" rel="stylesheet"/>
  <style type="text/css">
    body { background-image: none; }
  </style>
</head>
<body>
	<br/>
	<p>You don&#039;t have a Current CCR yet.  Create a CCR and save it to get started!</p>
</body>
</html>
  
