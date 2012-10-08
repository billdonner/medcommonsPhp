<?php

require_once "appsrvlib.inc.php";

require_once "alib.inc.php";

require_once "showbill.inc.php";

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo

$info = make_acct_form_components($accid);
$desc = "MedCommons Show Application Billing Events";
$title = "MedCommons Show Application Billing Events";
$startpage='';
$top = make_acct_page_top ($info,$accid,$email,$id,$desc,$title,$startpage,"");
$bottom = make_acct_page_bottom ($info);
//ccrlogview?fn=Jane&ln=Hernandez&email=jhernandez@foo.com etc&accid=12123123&from=StMungo
//this is just a crude hack to paint a page of hyperlinks to get to ccrs by user
list ($balance,$body) = showbill($accid);
$pp = prettyprice($balance);


$html = <<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="robots" content="all"/>
                <meta name="description" content="MedCommonsBilling Event Log"/>

        <title>MedCommons Billing App Event Log for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css"; </style>
        <script language="javascript" type="text/javascript">

        <!--
function paymentpopup(url) {
	newwindow=window.open(url,'../pay/payviacc.php','height=600,width=450,toolbar=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}

// -->
</script>
    </head>
    <body>
    $top
<h4>Billing Events for $accid for period from $firstdate to $lastdate (current balance: $pp)</h4>

$body
$bottom
    </body>
</html>


XXX;


echo $html;
?>
