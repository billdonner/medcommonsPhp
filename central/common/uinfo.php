<?php

$abbrev = stripslashes($_REQUEST['a']);

/* step 1 indicate whether logged on or not */

$mcid=""; $fn=""; $ln = ""; $email = ""; $from = "";
$c1 = $_COOKIE['mc'];
if ($c1=='')
$logoninfo = <<<XXX
<a href='http://secure.test.medcommons.net:8080/identity/login' target='_parent'>logon</a>
XXX;

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
	
	$logoninfo = <<<XXX
<a href="https://secure.test.medcommons.net/acct.php?accid=$accid&fn=$fn&ln=$ln&email=$email&from=$from" target='_parent'>$accid</a>&nbsp;<a href='logout.html' target='_parent'>logout</a>
XXX;
}



/* if no cert info available, just offer a link to the tls only site */
$certemail = $_SERVER["SSL_CLIENT_S_DN_Email"];
if ($certemail =='') $certinfo = "<a href='https://ops.medcommons.net' target='_parent'>tls</a>";
else {

	/* reveal cert info if we have it */


	$s1=$_SERVER["SSL_CLIENT_S_DN_CN"];
	$s2=$_SERVER["SSL_CLIENT_S_DN_Email"];
	$s3 = $_SERVER["SSL_CLIENT_S_DN_O"];
	$s4 = $_SERVER["SSL_CLIENT_S_DN_OU"];
	$s5=$_SERVER["SSL_SERVER_I_DN_CN"];
	if ($s5!='') $s5 = "issued by: ".$s5;
	$certinfo =<<<XXX
     <span>$s1 --$s3 $s4 --$s2 </span>
      <span>$s5</span>
XXX;
}
if ($abbrev=='1') {$logoninfo=''; $certinfo='';} // in abbrev mode, don't offer any links or cert info


if ($from!='')$from='idp='.$from;
	$x=<<<XXX
    <html><head><link href="main.css" rel="stylesheet" type="text/css"></head>
    <body>
    <div id="quickSummary">
    <p class="p2"> 
   $logoninfo $certinfo $fn $ln $email $from
    </p>
    </div></body></html>
XXX;
echo $x;
?>