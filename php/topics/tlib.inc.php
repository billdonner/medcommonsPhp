<?php
require_once 'mlib.inc.php';
require_once 'dbparams.inc.php';

function aconnect_db()
{
	$db=$GLOBALS['DB_Database'];
	mysql_pconnect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	return $db;
}
function testif_logged_in()
{
	if (!isset($_COOKIE['mc'])) //wld 10 sep 06 strict type checking
	return false;
	$mc = $_COOKIE['mc'];

	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	if ($mc!='')
	{
		$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
		$props = explode(',',$mc);
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
	}
	return array($accid,$fn,$ln,$email,$idp,$mc);
}

function aconfirm_logged_in($fail_if_not=false)
{
	// $fail_if_not is optional string that forces complete death if not logged on

	if (isset($GLOBALS['__mckey']))
	{
		list ($sha1,$accid,$email)=explode('|',base64_decode($GLOBALS['__mckey'])); //if starting automagically
		return array($accid,'','',$email,'','');
	}
	else

	if (!isset($_COOKIE['mc']))
	{
 		if ($fail_if_not) die($fail_if_not); 
		//header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
		//echo "Redirecting to MedCommons Web Site";
		$home = $GLOBALS['Homepage_Url'];
		$irl = $GLOBALS['Identity_Base_Url'];
		$trl = $GLOBALS['Commons_Url'].'trackinghandler.php';
		$errurl = $GLOBALS['Accounts_Url'].'goStart.php';
		if (isset($GLOBALS['Script_Domain'])) //svn 824 with enhanccement
		$domain = $GLOBALS['Script_Domain']; else $domain=false;
		$setDomain = "";
		if($domain && ($domain!= "")) {
			$setDomain = "document.domain = '$domain';";
		}
		$html=<<<XXX
		

<?xml version='1.0' encoding='US-ASCII' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=US-ASCII" />
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Home Page"/>
        <meta name="robots" content="all"/>

        <title>MedCommons - Interoperable and Private Personal Health Records</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        <style type="text/css" media="all"><!--

td {
    vertical-align: top;
    padding: 10px;
    border: 1px;
    border-style: solid;
    border-color: #fff #fff #ccc #ccc;
}

td p, td a {
    font-size: x-small;
    padding-bottom: 0px;
    margin-bottom: 0px;
}

#forgotten {
    padding-top: 0px;
    margin-top: 0px;
}

.label {
    font-size: x-small;
}

.error {
	background-color: #c00;
	color: #fff;
}
 
h4 {
    background-color: #ccc;
}

// --></style>
    </head>
     <body onload="$setDomain;" >
        <div id="container">
            <div id="intro">
			<a href="$home" ><img src='images/mclogotiny.png' alt="MedCommons"></a>
            </div>
            <div id="supportingText">
	        <h3>
                    <span>Sign In</span>
                </h3>
		<div id='login'>
		  <table><tr><td>

		    <form method='post' action='$irl/login'>
		        <h4>Existing Account</h4>
		  <!--<a class='label' href='$irl/register'>Create a New Account</a>-->
      <p style="font-size: 9px;">Please Note: New Registrations are currently disabled and will resume after a short tesing period currently in-progress.</p>
			<p>Your MCID or E-Mail Address:</p>
			<input name='mcid' size='19' value='' />

			<p>Your Password:</p>

			<input name='password' type='password' />
			<p id='forgotten'>
			    <a href='$irl/forgotten'>Forgotten Password?</a>
			</p>
			<input type='hidden' name='userId' value='' />
			<input type='hidden' name='sourceId' value='' />
			<input type='submit' value='Sign On>>' />
		    </form>

		    </td></tr></table>
		</div>

		<div id='viaTN'>
		  <table><tr><td>

		    <form method='post' action='$trl'>
		        <h4>Find CCR By Tracking #</h4>
			<p>Enter 12 Digit Tracking #</p>
			<input name='trackingNumber' size='19' value='' />
			<input type=hidden name='returnurl' value='$errurl' />
			<input type='submit' value='Lookup>>' />
		    </form>

		    </td></tr></table>
		</div>

            </div>
        </div>
        <div id="footer">
            <a href="http://validator.w3.org/check/referer" title="Check the validity of this
                site&#8217;s XHTML">xhtml</a> &nbsp; <a
                href="http://jigsaw.w3.org/css-validator/check/referer" title="Check the validity of
                this site&#8217;s CSS">css</a> &nbsp; <a
                href="http://creativecommons.org/licenses/by-nc-sa/1.0/" title="View details of the
                license of this site, courtesy of Creative Commons.">cc</a> &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
                title="Check the accessibility of this site according to U.S. Section 508">508</a>

            &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
                title="Check the accessibility of this site according to Web Content Accessibility
                Guidelines 1.0">aaa</a>
            <p class="p1">&#169; MedCommons 2006</p>
        </div>
    </body>
</html>
XXX;
		echo $html;
		exit;
	}

	// here if we have a cookie
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $cl="";

	$mc = $_COOKIE['mc'];
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$props = explode(',',$mc);
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


	return array($accid,$fn,$ln,$email,$idp,$cl);
}// record suggestions in the db and give adrian an email
?>