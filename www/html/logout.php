<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<?
/*
 * This file is a wrapper for the real logout which belongs to the 'account' services
 * and thus *may* reside on a different server.
 *
 * wld - hacked this to use a form and take us to the top target frame
 */


   include("dbparams.inc.php");

  $logout = $GLOBALS['Identity_Base_Url']."/logout";
  
$html=<<<XXX

<html><head><title>redirecting to medcommons logout service $logout</title></head>
<body onLoad="document.theform.submit()">

<form target="_top" name='theform' action='$logout'' method='get'>
</form>
</body></html>
XXX;

echo $html;

 ?>

