<?php
//ccrlogview?fn=Jane&ln=Hernandez&email=jhernandez@foo.com etc&accid=12123123&from=StMungo
//this is just a crude hack to paint a page of hyperlinks to get to ccrs by user
require_once "dbparamsidentity.inc.php";

function verify_logged_in()
{
	$mc = $_COOKIE['mc'];
	if ($mc =='')
	{ header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
	echo "Redirecting to MedCommons Web Site";
	exit;}
	return $mc;
}

if($GLOBALS['NO_CCRLOG_LOGIN_CHECK'] != true) {
  $mc = verify_logged_in();	
}
	
$idplogo = $_REQUEST['idplogo'];
if ($idplogo =='') $idplogo="MEDcommons_logo_246x50.gif";
$idpdomain = $_REQUEST['idpdomain'];
$idplogout = $_REQUEST['idplogout'];
$accid=$_REQUEST['accid'];
$from=stripslashes($_REQUEST['from']);
$stfrom = stripslashes($from); 



/* regrettably, the fancy css to do this all in xml doesn't work on IE, so we need to generate table rows */
$args="accid=$accid&from=$from";
$idplogout = "<a href=$idplogout>logout</a>";
$url = "setredccr.php?$args"; 

if ($idpdomain!='') $pinglist = "<img src = 'images/pinglogo.gif' alt='PingFederate'>";
else {$pinglist = ''; $idplogout='';}

// the midsection here is now supplied in an iframe

// ssadedin: acct server (this page) may be on different server
// to main www server.  Hence check config to see if absolute url is necessary.
if($GLOBALS['BASE_WWW_URL']) {
  $uinfoUrl = $GLOBALS['BASE_WWW_URL']."/uinfo.php";
}
else
  $uinfoUrl = "uinfo.php";
                                                  
$x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Log for $accid"/>
        <meta name="robots" content="all"/>
        <title>MedCommons Log for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css"; </style>
    </head>
    <body>
        <div id="container">
            <div id="intro">
                <div id="pageHeader">
                    <a href="$idpdomain">
                        <img alt="MedCommons" src="images/$idplogo"/>
                    </a>
                    
                    <iframe src="$uinfoUrl?a=0" height="50" width="246" scrolling="no" name="uinfo"
                        frameborder="0">user info</iframe>
                </div>
                <div id="quickSummary">
                    <p class="p2">
                        <span>a patient centric ccr transport and storage network</span>
                    </p>
                </div>
            </div>


                <div id="supportingText" title="provider: $fn $ln $accid $email $stfrom">
                    <div id="patientCCRLog">
                        <iframe class="mcLog" scrolling="no" width="730" frameborder="0"
                            src="ccrlogiframe.php?accid=$accid&from=$from">contacting MedCommons </iframe>
                        
                    </div>
                </div>    
    
            <div id="footer">
                <p class="p2">$ipdlogout</p>
                <p class="p1">&#169; $stfrom 2006</p>
                <p class="p2"><img src="images/MEDcommons_logo_246x50.gif"/>&nbsp;$pinglist</p>
            </div>
            <!-- Add a background image to each and use width and height to control sizing, place with absolute positioning -->
        </div>
    </body>
</html>


XXX;


echo $x;
?>
