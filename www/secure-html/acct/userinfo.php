<?php

// produce a table with all the user info MedCommons has stored about a user with specified account id
define ('INFOLEVEL',1);
define ('USERLEVEL',0);

require_once "alib.inc.php";
require_once "userinfo.inc.php";
$glevel = $_REQUEST['level'];
if ($glevel=='')$glevel=USERLEVEL;

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database
$info = make_acct_form_components($accid);
$desc = "MedCommons UserInfo";
$title = "MedCommons UserInfo for $accid $email";
$startpage='';
$top = make_acct_page_top ($info,$accid,$email,$id,$desc,$title,$startpage,"");
$bottom = make_acct_page_bottom ($info);

$body = userinfo($accid,$glevel);

$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons UserInfo for $accid"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons UserInfo for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        $styleline
        <script type="text/javascript" src="safeinstantedit.js"></script>
   </head>
    <body id="css-zen-garden"  >
    <div id="container">
    $top
  	$body
     </div>
     $bottom
</body></html>
XXX;
echo $html;
?>