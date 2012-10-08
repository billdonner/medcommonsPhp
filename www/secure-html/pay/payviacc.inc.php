<?php
function pconnect_db()
{
	$db=$GLOBALS['DB_Database'];
	mysql_pconnect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect in payviaacc to database $db ".mysql_error());
	return $db;
}
function pconfirm_logged_in()
{
	$mc = $_COOKIE['mc'];
	if ($mc =='')
	{
		//header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
		//echo "Redirecting to MedCommons Web Site";

		$irl = $GLOBALS['Identity_Base_Url'];
		$trl = $GLOBALS['Commons_Url'].'trackinghandler.php';
		$errurl = $GLOBALS['Accounts_Url'].'goStart.php';
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
        <meta name="description" content="MedCommons Payment Handling"/>
        <meta name="robots" content="all"/>

        <title>MedCommons -Payment Handling</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"><!--
@import "acctstyle.css";

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
    <body onload="document.domain='medcommons.net';" >
    <div><p>please logon to medcommons</p>
        </div>
       
    </body>
</html>
XXX;
		echo $html;
		exit;
	}


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
	return array($accid,$fn,$ln,$email,$idp,$cl);
}

function error_redirect($err)
{
	echo "error_redirect $err";
	exit;

}
function doprettyprice ($price)
{
	$dollars = intval($price/100);
	$cents = $price - $dollars*100;
	$tens = intval($cents/10);
	$ones = $cents -$tens*10;
//	echo "Price $price dollars $dollars cents $cents tens $tens ones $ones";
	$vprice = "$".$dollars.".".$tens.$ones;
	return $vprice;
}
function closebutton($s)
{
	return "<p><form><input type=button value='$s' onClick='javascript:window.close();'></form> </p>";
}
function frontmatter($s)
{
$html= <<<XXX
<html><head><title>MedCommons - Pay Via Credit Card</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "appsrv.css"; </style>
</head>
<body>
<table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../acct/images/mclogo.gif" 
                title="Pay by Credit Card" /></a>
                </td></tr>
                </table>
XXX;
return $html;
}


/**
 * Enter description here...
 *
 * @param unknown_type $price
 * @param unknown_type $accid
 * @param unknown_type $cc
 * @param unknown_type $returl
 */
function purchase ( $price, $accid, $cc, $returl)
{


	// find the cc info

	$select = "SELECT * FROM ccdata WHERE (accid ='$accid') AND (nikname='$cc')";
	$result = mysql_query($select) or error_redirect("sql err ".mysql_error());
	if (0==mysql_num_rows($result)) error_redirect("$accid-$cc not found in ccdata ".mysql_error());

	$l=mysql_fetch_assoc($result);

	$name = $l['name'];
	$address = $l['addr'];
	$city = $l['city'];
	$state = $l['state'];
	$zip = $l['zip'];
	$cardnum = $l['cardnum'];
	$expdate = $l['expdate'];
	$vprice = doprettyprice($price);
	$ccprice = $price/100.0;

	if ($cardnum=='9999999999999999') { //very special processing
	$action = "freeride.php";
	$blurb = "Taking a free ride....";
	}
	else {$action = "https://payments.verisign.com/payflowlink";
			$blurb = "Awaiting Verisign Secured Payment Processing....";
	}
	// build the message for verisign
	$x=<<<XXX
<HTML>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
 Copyright 2006 MedCommons Inc.   All Rights Reserved.
-->
  <head>
    <title>MedCommons Payflow Payment</title>
     <meta name="robots" content="none">
    <script language="javascript">
      function init() {
      foo.submit();
      }
    </script>
  </head>
<BODY onload="init()">
<img src='http://www.medcommons.net/images/smallwhitelogo.gif'>
<h4>$blurb</h4>
<form method="POST" name = "foo" id = "foo" action="$action">
<input type="hidden" name="LOGIN" value="medcommons">
<input type="hidden" name="PARTNER" value="VeriSign">
<input type="hidden" name="AMOUNT" value="$ccprice">
<input type="hidden" name="TYPE" value="S">
<input type="hidden" name="DESCRIPTION" value="MedCommons Account $accid Payment">
<input type="hidden" name="USER1" size="16" value="$vprice">
<input type="hidden" name="USER2"  value="$returl">
<input type="hidden" name="USER3"  value="$accid">
<input type="hidden" name="USER4"  value="$nowtime">
<input type="hidden" name="CUSTID" value="12345678">
<input type="hidden" name = "SHOWCONFIRM" value="False">
<input type="hidden" name = "ECHODATA" value="True">
<input type="hidden" name="NAME" value="$name">
<input type="hidden" name="ADDRESS" value="$address">
<input type="hidden" name="CITY" value="$city">
<input type="hidden" name="STATE" value="$state">
<input type="hidden" name="ZIP" value="$zip">
<input type="hidden" name="CARDNUM" value="$cardnum">
<input type="hidden" name="EXPDATE" value="$expdate">
</form>
</body>
</html>
XXX;
	echo $x;
	exit;
}
?>