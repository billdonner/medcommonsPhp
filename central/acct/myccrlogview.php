<?php
//ccrlogview?fn=Jane&ln=Hernandez&email=jhernandez@foo.com etc&accid=12123123&from=StMungo

//this is just a crude hack to paint a page of hyperlinks to get to ccrs by user
require_once "dbparams.inc.php";
function prober ($url, $accid,$idp){

	$db=$GLOBALS['DB_Database'];

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	$idpclause = "and (ccrlog.samlidp ='$idp') ";
	if ($idp=='')$idpclause="";
	$query = "SELECT * from ccrlog where (accid = '$accid') $idpclause;";
//echo "idp is $idp idpclause is $idpclause  ";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
//	echo "numrows is $rowcount";
	$errcount=0; $blurb = ""; 
	$emit = "";
	if ($result=="") {$emit= "?no accounts?"; return $emit;}

	while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$id = $l['id']; //record id
		$date = $l['date'];
		$samlidp = $l['samlidp'];
		$from = $l['src'];
		$to= $l['dest'];
		$subject = $l['subject'];
		$guid = $l['guid'];
		$status = $l['status'];
		$whereavailable = "only to the patient";
		if ($samlidp!='') $whereavailable = "to the patient and provider $samlidp";
		if ($status=='RED') $rowclass = "class='emergencyccr' 
		title='this ccr will be offered on the back of your healthcare card for emergency use'"; 
		else $rowclass=" title = 'this ccr is available $whereavailable'";
		$freeride = "&p=99999";
		if ($samlidp=='') $freeride = '';
		$emit .= <<<ZZZ
		       <tr $rowclass><td  title="make this CCR your Emergency CCR">
	<a  href="$url&id=$id"><img src="images/b_edit.png" /></a></td>
                                <td>$samlidp</td>
                                <td>$date</td>
                                   <td>
   <a  href="https://gateway001.private.medcommons.net:8443/router/tracking.jsp?tracking=$guid$freeride">$guid</a>                                </td>
                           <td>$to</td>
                          <td>$subject</td></tr>
ZZZ;

	}
	

	mysql_free_result($result);

	//errcount>0
	mysql_close();
//	echo "returning emit $emi
//	echo "return $emit";
	return $emit;
}

	
/*** start of main program ***/


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
	
	
$idplogo = $_REQUEST['idplogo'];
if ($idplogo =='') $idplogo="MEDcommons_logo_246x50.gif";
$idpdomain = $_REQUEST['idpdomain'];
if ($idpdomain =='') $idpdomain="www.medcommons.net";
$fn=$_REQUEST['fn'];
$ln=$_REQUEST['ln'];
$email=$_REQUEST['email'];
$accid=$_REQUEST['accid'];
$from=stripslashes($_REQUEST['from']);



/* regrettably, the fancy css to do this all in xml doesn't work on IE, so we need to generate table rows */
$args="fn=$fn&ln=$ln&email=$email&accid=$accid&from=$from";

$url = "setredccr.php?$args"; 




/* regrettably, the fancy css to do this all in xml doesn't work on IE, so we need to generate table rows */

$mid = prober($url, $accid,$from);
if ($from=='')
 $content = <<<XXX
  <ul > 
                            <li>For security purposes a copy of this page will be emailed to $email 
                                <a href="changemail.php">(change)</a> every time it is accessed.</li> 
                            <li><a href="changepwd.phpl">(change)</a> password for this page.</li> 
                        </ul>
                        <h4>The private documents in this account may be accessed</h4>
                        <ul>   
                            <li>Directly by the health care organization that created or received a specific document</li> 
                            <li>By entering a Tracking Number and PIN for a specific document (click above and  supply PIN)
                            </li> 
                            <li>By entering a Temporary Account Access Code as sent to your cellphone $cellphone <a href="changecell.php">(change)</a> </li>
                            <li>Online using a MedCommons recognized personal authentication token (Learn More)</li>
                            <li>By receipt of a Secret Account Access Code via Us Mail to:
                                <ul>
                                    <li>$fn $ln</li><li>$street</li><li>$city $state $zip <a href="changeaddr.php">(change)</a>
                                    </li>
                                </ul>
                            </li>
                            <li>By receipt of a DVD with the account contents delivered to the address above</li>
                            <li>Please be prepared to supply the Secret Account Access Code if you contact MedCommons Support at 1-800-555-1212</li>
                        </ul>
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
        <meta name="description" content="My MedCommons Log for $accid"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons Account for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "main.css"; </style>
    </head>

<body>
        <div id="container">
            <div id="intro">
                <div id="pageHeader">
                    <a href="$idpdomain"><img alt="MedCommons" src="images/$idplogo"/></a>
                    <iframe src="uinfo.php" height="50"  width="246" scrolling="no" name="uinfo" frameborder="0" ></iframe>
                </div>
                <div id="quickSummary">
                    <p class="p2">
                        <span>a patient centric ccr transport and storage network</span>
                    </p>
                </div>
                <div id="personalInfo">
                    <h3>$fn $ln </h3>
                    <p class="p1">Acct ID: $accid</p>
                    <p class="p1">$email $from</p>
                </div>
            </div>
            <div id="supportingText" title="$fn $ln $accid $email $from">
                <div id="patientCCRLog">
                    <h3>
                        <span>My MedCommons Account</span>
                    </h3>
                    <div id="tableSummary" title="click on any tracking number to view">
                        <p class="p2">
                            <span>select a Tracking Number to view the CCR or a Red Pencil for Emergency CCR</span>
                        </p>
                    </div>
                    <p class="p1">
                        <table>
                            <tr>
                            <th title="set Emergency CCR">red</th><th>idp</th>
                                <th>date</th>
                                <th>tracking #</th>
                                <th>to</th>
                                <th>email notification subject</th>
                            </tr>
                            $mid
                        </table>
                    </p>
                </div>
            </div>    
                    <div id="content"> 
                       	$content 
                        <p class="p1">Access to this MedCommons Account is covered by our <a href="http:www.medcommons.net/termsofuse.html">terms of use</a></p>
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
            <!-- Add a background image to each and use width and height to control sizing, place with absolute positioning -->
            
    </body>
XXX;


echo $x;
?>