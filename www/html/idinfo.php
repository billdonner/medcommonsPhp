<?php

    include("dbparams.inc.php");


  require_once("JSON.php");

  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");

$file = stripslashes($_REQUEST['a']);

/* step 1 indicate whether logged on or not */

$mcid=""; $fn=""; $ln = ""; $email = ""; $from = "";
$c1 = $_COOKIE['mc'];
if ($c1=='') {
  $logoninfo = <<<XXX
  <a href="loginredir.php">Logon&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
XXX;
}
else {

	$mcid=""; $fn=""; $ln = ""; $email = ""; $from = "";
	$props = explode(',',$c1);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;

			case 'fn': $fn = $val; break;

			case 'ln': $ln = $val; break;

			case 'email'; $email = $val; break;

			case 'from'; $from = stripslashes($val); break;

		}

	}
//	$aw = $GLOBALS['Accounts_Url']."/acct.php?accid=$accid&from=$from"; //
	$aw = $GLOBALS['Accounts_Url']."goStart.php";
	$logoninfo = <<<XXX
   <a href='logout.php'  target='_top' onclick='showContentFrame();'>Logout</a>
XXX;
}

if ($file!="") $file=file_get_contents($file.'.htm');

if ($from!='')$from='idp='.$from;

$line1 = substr($ln.",".$fn,0,16);
$line2 = $email." ".$from;
$len = strlen($line2);
$maxlen=20;
if($len>$maxlen) {
 $line2 = substr($line2,0,$maxlen-3)."...";
}
// make the logged in link replace the whole top window (was contentwindow)
if ($c1!='') { // logged in
  $statusinfo="<div id='statusinfo'><p>$line1</p><p>$line2</p></div></div>";
  $accountLink="<div id='accountLink'><a id='accountLinkA' href='$aw' target='_top' onclick='showContentFrame();'>$accid</a></div>";
  $classinfo="class='bulletLink loggedIn'";
  $json = new Services_JSON();

  $accountInfo->accountId = $accid;
  $accountInfo->email = $email;
  $accountInfo->lastName = $ln;
  $accountInfo->firstName = $fn;
  $accountInfoOut = $json->encode($accountInfo);
}

$domain = $GLOBALS['Script_Domain'];

	$x=<<<XXX
<?xml version='1.0' encoding='UTF-8'?><ajreturnblocks><account>$accountInfoOut</account><status><span class='bulletLink'><ul $classinfo ><li id="logonLink">$logoninfo</li></ul></span><div id="accountOuter">$accountLink</div><div id='statusouter'>$statusinfo</div></status><domain>$domain</domain>
XXX;
if ($file!="") $x.="<content>".$file."</content>";
	$x.="</ajreturnblocks>";
echo $x;
?>
