<?php
require_once "dbparamsidentity.inc.php";
require_once "ggccrloglib.inc.php"; // the hard work is all in here

//start here from google when running as a gadget

$mckey= $_REQUEST['up_mckey'];
list ($sha1,$accid,$email)=explode('|',base64_decode($mckey));


if(! $GLOBALS['NO_CCRLOG_LOGIN_CHECK']) {
    //    $mc = verify_logged_in(); // return cookie value, or disappear quietly if not logged on
}


$miniview = true;


// do a bunch of database reads to get rows from ccr log, sorted by idp

$count = readdb($miniview,$accid,$from,$content,$tab,$emailbuf,$fn,$ln,$email,$street1,$street2,
$city,$state,$postcode,$country,$mobile,$emergencyccr,$patientcard,$einfo,$trackerdb);

// tell the user via email his page was viewed

//$ajstatline =  notifyuser($email,$accid,$fn,$ln,$emailbuf);

// put together tab0
$tab0content = ($miniview? '':tab0(true,$email,$mobile,$ln,$fn,$street1,$street2,$city,$state,$postcode));

// assemble all the tabs
$alltabs = assembletabs($miniview,$count,$content,$tab,$tab0content);
// put whole page out for the browser

echo wholepage($patientcard,$emergencyccr, $alltabs, $accid, $fn, $ln, $email, $idpdomain, $idplogo,"initialized",$einfo);

exit;

function wholepage ($patientcard,$emergencyccr, $content, $accid, $fn, $ln, $email, $idpdomain, $idplogo,$ajstatline,$einfo)
{    
  $wwwUrl =$GLOBALS['BASE_WWW_URL'];
  $logoutlink ="<a href='$wwwUrl/logout.html' target='_parent'>logout</a>";
    $lasttime = time(); // pass this in as the time the server satisfied this request

     $secureHost = $GLOBALS['Commons_Url'];

     
  $einfoDeclaration = '';
  if($einfo != '') {
    $einfoDeclaration = "window.einfo = evalJSON('$einfo');";
  }

$c1 = $_COOKIE['mc'];     
$theme = $_COOKIE['theme'];     
$prettyAccId = prettyaccid($accid);

// Set script domain if option configured to allow cross site javascript to work
$domain = $GLOBALS['Script_Domain'];
if($domain && ($domain!= "")) {
  $setDomain =<<<XXX
    document.domain = '$domain';
XXX;
}


    $x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="My MedCommons Log for $accid"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons Account for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "ggstyle.css"; </style>
        <script src="MochiKit.js" type="text/javascript"></script>
        <script src="utils.js" type="text/javascript"></script>
        <script src="tabs.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="tabs.css"/>
        <link rel="stylesheet" type="text/css" href="$theme.css"/>
        <script type="text/javascript">
          this.secureHost = "$secureHost";
          this.wwwHost = "$wwwUrl";
          $einfoDeclaration;
          $setDomain;
        </script>
        <script src="ajform.js"  type="text/javascript"></script>
        <style type="text/css">
          #trackingBox,#quickSummary,#intro {
            display: none;
          }
        </style>
    </head>

<body onload="initMyCCRLog('$accid','$lasttime');" style='margin: 0 0px 0 0px;' >
  <div id="widecontainer">
  <table><tr><td><a href="index.html" target="_parent" ><img border="0" alt="MedCommons" 
                src="images/mclogotiny.png" 
                title="MedCommons - A Patient Centric CCR Transport and Storage Service" /></a>
                </td><td>$prettyAccId</td></tr>
                </table>
    <div id="patientCardOuter">
            $patientcard
      </div>
           <div id='emergencyccr' class='rounded'> $emergencyccr</div>
              </div>
     
                    <div id="content" > 
                       $content
                    </div> 
         </div>
    </body>
    </html>
XXX;
    return $x;
}

?>
