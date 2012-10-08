<?php

require_once "alib.inc.php";


list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not lo

$db = aconnect_db(); // connect to the right database



$query = "Select rolehack from users WHERE (mcid = '$accid')";
$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
$f = mysql_fetch_assoc($result);
$role=$f['rolehack'];


switch ($role)
{
case 'open' : $p = $GLOBALS['Accounts_Url']."flatPageFull.php";break;
case 'pradmin' : $p = $GLOBALS['Accounts_Url']."flatPageAdmin.php";break;
case 'patient' : $p = $GLOBALS['Accounts_Url']."myPage.php";break;
case 'provider' : $p = $GLOBALS['Accounts_Url']."providerPage.php";break;
default:   $p = $GLOBALS['Homepage_Url']; break;
}
//onLoad="document.theform.submit()"
$html=<<<XXX
<html><head><title>redirecting to $p id is $accid via form submit</title></head>
<body onLoad="document.theform.submit();">

<form target="_top" name='theform' action='$p' method='post'>
</form>
</body></html>
XXX;
echo $html;

exit();

?>