<?php 

require_once "dbparamsidentity.inc.php";
/*** goes to the users main account page based upon his current logged on account id ****/


// start here 
$cl = $_COOKIE['mc'];
$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
$c1 = $_COOKIE['mc'];
if ($c1!='')
{

	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$props = explode(',',$c1);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;
			case 'fn': $fn = $val; break;
			case 'ln': $ln = $val; break;
			case 'email'; $email = $val; break;
			case 'from'; $idp = stripslashes($val); break;
		}

	}
echo "mcid is $accid ($idp) $email $fn $ln";
}
else echo "mc cookie not found";
exit();

?>
