<?php

// produce a table with all the user info MedCommons has stored about a user with specified account id
define ('INFOLEVEL',1);
define ('USERLEVEL',0);

require_once "alib.inc.php";
require_once "puinfo.inc.php";
if (isset($_REQUEST['level'])) $glevel = $_REQUEST['level']; else $glevel='';
if ($glevel=='')$glevel=USERLEVEL;

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database
$info = make_acct_form_components($accid);
$desc = "MedCommons Persona Info";
$title = "MedCommons Persona  for $accid $email";
$startpage='';
$top = make_acct_page_top ($info,$accid,$email,'',$desc,$title,$startpage,"");
$bottom = make_acct_page_bottom ($info);

$body = puinfo($accid,$glevel);
$styleline = '';
$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Personas"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons Personas for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        $styleline
        <script type="text/javascript" src="safeinstantedit.js"></script>
             <script type="text/javascript" >
               <!-- 
function paymentpopup(url) {
	newwindow=window.open(url,'_payment','height=600,width=450,toolbar=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
function personapopup(url) {
	newwindow=window.open(url,'_persona','height=300,width=500');
	if (window.focus) {newwindow.focus()}
	return false;
}

// --> 
			</script>
        
   </head>
    <body id="css-zen-garden"  >
    <div id="container">
    $top
  	$body
     $bottom
	</div></body></html>
XXX;
echo $html;
?>