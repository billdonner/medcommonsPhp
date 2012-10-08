<?php

function my_make_group_page_top ($info, $accid,$email, $id,$desc,$title,$startpage)
{
	if ($info->leftphotourl!='') $leftphotoblock="<td align=left>$info->leftphotourl</td>";
	if ($info->rightphotourl!='') $rightphotoblock="<td alight=right>$info->rightphotourl</td>";
//	if ($startpage=='') $sp=''; else  $sp="<a href=../../acct/setStart.php?p=$startpage?id=$id>mark</a>&nbsp;";
	$iden =   "<a href='../../acct/goStart.php'>$accid</a>";
	$x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
     <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content='$desc'/>
        <meta name="robots" content="all"/>
        <title>$title</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "../groups.css"; </style>
    <link rel="stylesheet" type="text/css" href="autoComplete.css"/>
    <script type="text/javascript" src="MochiKit.js"> </script>    
    <script type="text/javascript" src="utils.js"> </script>    
    <script type="text/javascript" src="autoComplete.js"> </script>   
    </head> 
    <body><div class='widecontainer'> <div class="header"><h3>$practicename</h3></div>
XXX;
	return $x;
}
require_once "args.inc.php";

// practice group admin page
require_once "grlslib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
$practicegroupid = $_REQUEST['pid']; //specifies the practicegroup

$select = "SELECT providergroupid,patientgroupid from practice where practiceid='$practicegroupid'";
$res = mysql_query($select);
$result = mysql_fetch_array($res);
$providersid = $result[0];
$patientsid = $result[1];

confirm_admin_access($accid,$providersid); // does not return if this user is not a group admin

// make tooltip list

$info = make_group_form_components($providersid);
$desc = "MedCommons Internal Patient Registry";
$title = 'MedCommons Record Locator Service';
$startpage ="groups/grls/query.php?pid=$providersid";
$top = my_make_group_page_top ($info,$accid,$email,$id,$desc,$title,$startpage);
$int = 10;

if ($limit=='') $limit=20; else if ($limit>20) $limit=20;
$lasttime = cleanreq('lt');
if ($lasttime=='') $lasttime=0;
require_once "where.inc.php";

$isajax = ($int!=0);
$wherestring = ($isajax?"ajax ":"noajax ")."first wc:$wc limit:$limit int:$int query:$whereclause";

require_once "content.inc.php";
$synch = time();
// make a html header and insert the first round of content in the body
{ //prepare queryparams, which will come back around to us on the next ajax invokation
//$params = "pfn=$pfn&pgn=$pgn&pid=$pid&pis=$pis&spid=$spid&rpid=$rpid&dob=$dob&int=$int&limit=$limit&logo=$logo";

$params = "gid=$practicegroupid&PatientFamilyName=$pfn&PatientGivenName=$pgn&PatientIdentifier=$pid&PatitentIdentifierSource=$pis&SenderProviderId=$spid&ReceiverProviderId=$rpid&DOB=$dob&int=$int&limit=$limit";
// first time paint
// if it wont be repeating, we can skip the slow init
if ($int==0) $onload=''; else
$onload = <<<XXX
onload="initAjaxPage('$params','$int','$synch');"
XXX;
ob_start();
?>  <script type="text/javascript">
function start() {
	initAjaxPage('<?echo $params?>', '<?echo $int?>', '<?echo $synch?>');
}
addLoadEvent(start);
  </script>
<?
if($int!=0) {?>  
  <script src="ajlib.js"  type="text/javascript"></script>
  <script type="text/javascript">
  function start() {
  	initAjaxPage('<?echo $params?>', '<?echo $int?>', '<?echo $synch?>');
  }
  addLoadEvent(start);
  </script>
<?}
$body = ob_get_contents();
ob_end_clean();


$newUrl = $GLOBALS['RLS_Default_Repository'].'/tracking.jsp?tracking=new&registry=jaroka&idp=jaroka';
$rlsName =  $GLOBALS['RLS_Name'];
$body.= <<<XXX
<div>$st</div>   
<div id="content"> 
  <button name="newRecord" style="float: right;" 
    onclick="window.open('$newUrl','newccr','width=780')">New Patient&nbsp;<img style="vertical-align: middle;" src="images/new.png"/></button>
  <div id="records">
    $content
  </div>
</div> 
<div id='acdiv' ></div>
</body></html>
XXX;

}// end of first time paint
$bottom = make_group_page_bottom ($info);
echo $top.$body.$bottom;

?>
