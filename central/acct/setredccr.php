<?php
$id = $_REQUEST['id'];

$fn=$_REQUEST['fn'];
$ln=$_REQUEST['ln'];
$email=$_REQUEST['email'];
$accid=$_REQUEST['accid'];
$from=$_REQUEST['from'];

/* regrettably, the fancy css to do this all in xml doesn't work on IE, so we need to generate table rows */
$args="fn=$fn&ln=$ln&email=$email&accid=$accid&from=$from";

$returnurl = "myccrlogview.php?$args"; 

$x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="Emergency CCR Access Service"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="Emergency CCR Access Service"/>
        <meta name="robots" content="all"/>
        <title>Emergency CCR Access Service</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <style type="text/css" media="all"> @import "main.css"; </style>
    </head>
    <body bgcolor="#ffdfdf">
        <div id="container">
           
            <div id="supportingText" title="Emergency CCR Access Service ">
                <div id="preamble">
                    <h3>Emergency CCR Access Service</span>
                    </h3>
                    <p class="p1">Please Confirm that you wish to set your Emergency CCR  </p>
                 </div>
         
         </div>
             <div id="emergencyCCRBox">
                <form method="post"
                    action="finishredccr.php">
                    <input type="hidden" name="returnurl"
                        value="$returnurl" /> 
                        <input type="hidden" name="id" size="16" value="$id"/>
                     <input type="hidden" name="accid" size="16" value="$accid"/>
                     <input type="hidden" name="fn" size="16" value="$fn"/>
                     <input type="hidden" name="ln" size="16" value="$ln"/>
                     <input type="hidden" name="email" size="16" value="$email"/>
                     <input type="hidden" name="from" size="16" value="$from"/>
               <input type="submit" name="submit" value="Set Emergency CCR"/>
                    <input type="submit" name="submit" value="Cancel"/>
                </form>
            </div>
        <div id="footer">
            <p class="p1">&#169; Emergency Rescue Access Service 2006</p>
        </div>
    </body>
</html>
XXX;
echo $x;
?>