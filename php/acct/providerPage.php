<?php
//this is where providers come



// $__flat == give the page a flat look with no toggle options

// build standard account page, as a flat page of separate sections
// the sections are specified via the ?s param, which is a list of single char abbrevs for different sections
//

require_once "alib.inc.php";

require_once "layout.inc.php";
// show groups if we have any




function iter_practices ($accid)
{
	// GROUP Practices I am a healthcare member of
	$mod = "<!-- begin pratice membership section -->";
	$head = '';
	$out1="<div>$mod<table class='trackertable'>
                <tr>$head<th>group</th><th>my Identity</th></tr>";
	$query = "SELECT * from practice q, groupmembers p, groupinstances i , users u
	where p.memberaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
	i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
	p.memberaccid=u.mcid order by q.practicename ";

	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true; $rowclass='';

	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			if ($rowcount==1) return array ($a['practiceid'],false);
			$odd = !$odd;

			$gid = $a['practiceid'];
			$logo = "<a href='".$a['practiceRlsUrl']."?pid=$gid'><img src='".$a['practiceLogoUrl']."' alt='".$a['practicename']."' /></a>";
			$out1.="<tr class='$rowclass'><td>".
			$logo."</td>".
			"<td>".$a['practicename']."</td>".
			"</tr>";
		}
		$out1 .="</table>";
	}
	$out1 .= "</div>";

	return array(false,$out1);
}

/////////////// main program starts here /////////////////

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo

$__flat = true;


$db = aconnect_db(); //not sure why we have to re-connect, but lets use other library
list($idx,$html) = iter_practices($accid);
if ($html==false)
{
	// if just one, go there
	$__practicegroupid = $idx;
	$__title = "MedCommons Provider Page for $fn $ln - $email $accid";
	require_once "rls.php";
	exit;

};

// otherwise, show the table

$layout =  stdlayout($html);

$styleline = ''; //forthcoming
$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="Provider Page"/>
        <meta name="robots" content="all"/>
        <title>Provider Page for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        $styleline
           <script src="MochiKit.js" type="text/javascript"></script>
        <script src="tabs.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="tabs.css"/>
        <script type="text/javascript" src="blender.js"></script>
        <script src="utils.js" type="text/javascript"></script>
   </head>
    <body id="css-zen-garden"  >
    <div id="container">
 $layout
    </div>
    </body>
    </html>
XXX;
echo $html;
?>
