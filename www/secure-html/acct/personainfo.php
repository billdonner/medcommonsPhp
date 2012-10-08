<?php
//
//if ($GLOBALS['__refreshtop']) $extra = "onload='window.opener.location.reload(true);'";
//else $extra=''; //refresh parent if re-invoked

require_once "persona.inc.php";

require_once "alib.inc.php";

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database

$num = $_REQUEST['id'];
$body = showpersona ($accid,$num);


$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons User Persona"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons User Persona for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>

   </head>
    <body id="css-zen-garden" $extra >

   $body
   </div>
        <div id="footer">
            <p class="p1">&#169; MedCommons 2006</p>
        </div>

 
XXX;
echo $html;

?>