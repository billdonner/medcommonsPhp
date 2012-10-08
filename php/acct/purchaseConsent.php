<?php
// consent to purchase


require_once "alib.inc.php";
require_once "appsrvlib.inc.php";


// start here
$appserviceid = $_REQUEST['s'];
$istuff = $_REQUEST['i']; // init rtn and stuff

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database
$info = make_acct_form_components($accid);
$desc = "MedCommons Consent to Purchase Services";
$title = "MedCommons Consent to Purchase Services";
$startpage='';
$top = make_acct_page_top ($info,$accid,$email,'',$desc,$title,$startpage,"");

$bottom = make_acct_page_bottom ($info);

$query = "SELECT name from appservices where '$appserviceid'=appserviceid";

$result = mysql_query ($query) or die("can not query  $query - ".mysql_error());

$aname = mysql_fetch_row($result);

$product = $aname[0]; 

mysql_free_result($result);

check_add_dependencies ($accid, $appserviceid); // does not return if problem
$chargeclass = billingclass($accid);
// this query is more precise than  the one in show app events
$query = "SELECT * from  appservices a, appservicechargeclasses c
where a.appserviceid = '$appserviceid' and c.appserviceid=a.appserviceid and
c.chargeclass = '$chargeclass' ";

$result = mysql_query ($query) or die("can not query join $query - ".mysql_error());
$rowcount = mysql_num_rows($result);
$aid[]=''; // always have something so in_array doesn't fail
$odd = false; $totalcost = 0.0; $balance = 0.0; $first = true;
if ($rowcount != 0) {
	while (true) {
		$a = mysql_fetch_array($result,MYSQL_ASSOC);
		if ($a=='') break;
		$odd = !$odd;
		$ct = $a['time'];
		$time = strftime('%T',$ct);
		$date = strftime('%D',$ct);
		if ($first) {
			$first = false; $firstdate = $date; $out = "<table class=trackertrable>";
		}
		$lastdate = $date;
		// figure out the cost initially it is just the setup and the permonth

		$cost = $a['setup']+$a['permonth'];
		$product = $aname;

		
			$balance += $cost;
			$totalcost += intval($cost);
			$rowclass = ($odd?"odd":"even");
			$out.="<tr><td>Service</td><td>".$aname."</td></tr>";
			$out.="<tr><td>Publisher</td><td>".$a['publisher']."</td></tr>";
			$out.="<tr><td>Description</td><td>".$a['description']."</td></tr>";
			if ($a['setup']!=0)
			$out.="<tr><td>Initial setup fee</td><td>".prettyprice($a['setup'])."</td></tr>";
			if ($a['permonth']!=0)
			$out.="<tr><td>Monthly rental fee</td><td>".prettyprice($a['permonth'])."</td></tr>";
			if ($a['perstoredgb']!=0)
			$out.="<tr><td>Per gb stored per month</td><td>".prettyprice($a['perstoredgb'])."</td></tr>";
			if ($a['perxmtgb']!=0)
			$out.="<tr><td>Per gb transmitted</td><td>".prettyprice($a['perxmtgb'])."</td></tr>";
			if ($a['perrcvgb']!=0)
			$out.="<tr><td>Per gb received</td><td>".prettyprice($a['perrcvgb'])."</td></tr>";
			if ($a['perclick']!=0)
			$out.="<tr><td>Per click fee</td><td>".prettyprice($a['perclick'])."</td></tr>";


	}
} else $out="<p>There is no charge for this extension for this chargeclass</p>";

$pp = prettyprice($balance);

    $body = <<<XXX
        <div id="container">
            <div id="intro">
                <div id="pageHeader">
                    <h3>Consent to purchase $product</h3>                    
					</div>
					<p>I agree to purchase $product as specified in the following payment schedule. More details will be supplied</p>
					<p>The initial charge to my account $accid is $pp</p>
            </div>
		$out
        <tr><td>
           <form method=post action=appadd.php>
           <input type=hidden name=s value='$appserviceid'>
           <input type=hidden name=i value='$istuff'>
           <input type=submit value=purchase>
           </form>
           </td><td>
           <form method=get action=appservices.php>
           <input type=submit value=cancel>
           </form></td>
           </tr>
           </table>
           </div>
XXX;


$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Purchase Consent"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons Purchase Consent for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        <script type="text/javascript" src="safeinstantedit.js"></script>
   </head>
    <body id="css-zen-garden"  >
    <div id="container">
    $top
  	$body
     </div>
     $bottom</body></html>
XXX;
echo $html;
?>