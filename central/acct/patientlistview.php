<?php
//patientlist.php?fn=Jane&ln=Hernandez&email=jhernandez@foo.com etc&accid=12123123&from=StMungo

//this is just a crude hack to paint a page of hyperlinks to get to ccrs by user
require_once "dbparams.inc.php";
function prober ($accid,$idp,&$idplogo,&$idpdomain,&$idplogout){

	$db=$GLOBALS['DB_Database'];

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	// get information on the IDP

	$query = "SELECT * from identity_providers WHERE (name='$idp')";
	$result = mysql_query ($query) or die("can not query table identity_providers - ".mysql_error());

	if ($result=="") {$emit= "?no match in identity_providers table?"; return $emit;}
	$l = mysql_fetch_array($result,MYSQL_ASSOC);

	$idplogo = $l['logo'];
	$idpdomain = $l['domain'];
	$idplogout = $l['logouturl'];
	
return $emit;
}



/*** start of main program ***/
$fn=$_REQUEST['fn'];
$ln=$_REQUEST['ln'];
$email=$_REQUEST['email'];
$accid=$_REQUEST['accid'];
$stfrom=stripslashes($_REQUEST['from']);
$from=$_REQUEST['from'];
/* regrettably, the fancy css to do this all in xml doesn't work on IE, so we need to generate table rows */

$mid = prober($accid,$_REQUEST['from'],$idplogo,$idpdomain,$idplogout);
$el = <<<XXX
<li>Bill Donner <a href="ccrlogview.php?idplogo=$idplogo&idpdomain=$idpdomain&from=$from&accid=8869786468062289&fn=Bill&ln=Donner&email=billdonner@medcommons.net&idplogout=$idplogout">go</a></li>
XXX;
                    
                    
    
$x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="$stfrom Patient List Page for Dr. $fn $ln"/>
        <meta name="robots" content="all"/>
        <title>$stfrom Patient List Page for Dr. $fn $ln</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "main.css"; </style>
    </head>

<body>
        <div id="container">
            <div id="intro">
                <div id="pageHeader">
               <a href="http://$idpdomain"><img alt="$stfrom" src="images/$idplogo"/></a>                   <iframe src="uinfo.php?a=1" height="50"  width="246" scrolling="no" name="uinfo" frameborder="0" ></iframe>

               </div>
                <div id="quickSummary">
                    <p class="p2">
                        <span>this page is synthentically forumulated with real patients</span>
                    </p> <p class="p2">
                        <span>provider: $fn $ln $accid $email $stfrom</span>
                    </p>
                </div>
                           </div>
            <div id="supportingText" title="provider: $fn $ln $accid $email $stfrom">
                <div id="patientCCRLog">
                                    <h3> 
                        <span>$fn $ln at $stfrom</span>
                    </h3>
                    <ul>
                    <li><a>view email</a></li>
                    <li><a>today's news</a></li>
                    <li><a>upcoming seminars</a></li>
                    </ul>
                    <h3>
                        <span>View Patients </span>
                    </h3>
                    <ul> 
                    <li>Jane Doe <a href="ccrlogview.php?idplogo=$idplogo&idpdomain=$idpdomain&idplogout=$idplogout&from=$from&accid=0240223147727995&fn=Jane&ln=Doe&email=janedoe@aol.com">go</a></li>
                    <li>Joe Bloggs <a href="ccrlogview.php?idplogo=$idplogo&idpdomain=$idpdomain&idplogout=$idplogout&from=$from&accid=0142879350152156&fn=joe&ln=bloggs&email=joebloggs@gmail.com">go</a></li>
					$el
                    </ul>

                  </div>
            </div>    
                    
            <!-- These extra divs/spans may be used as catch-alls to add extra imagery. -->
            <div id="footer"> <p class="p2"><a href=$idplogout>logout</a></p>
                              <p class="p1">&#169; $stfrom 2006</p>
                              <p class = "p2"><img src = "images/MEDcommons_logo_246x50.gif">&nbsp;<img src = "images/pinglogo.gif" alt="PingFederate"></p>
            </div>
            <!-- Add a background image to each and use width and height to control sizing, place with absolute positioning -->
            
    </body>
XXX;


echo $x;
?>