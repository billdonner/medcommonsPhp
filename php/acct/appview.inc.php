<?php		

require_once "alib.inc.php";
require_once "appsrvlib.inc.php";
function purchaseinfo($accid,$appservicename)
{

	$chargeclass = billingclass($accid);
	// this query is more precise than  the one in show app events
	$query = "SELECT * from  appservices a, appservicechargeclasses c
where a.name= '$appservicename' and c.appserviceid=a.appserviceid and
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
			if ($first) {$first = false; $firstdate = $date;}
			$lastdate = $date;
			// figure out the cost initially it is just the setup and the permonth

			$cost = $a['setup']+$a['permonth'];
			$product = $a['name'];
			$balance += $cost;
			$totalcost += intval($cost);
			$rowclass = ($odd?"odd":"even");
			$out.="<tr><td>Service</td><td>".$a['name']."</td></tr>";
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
	} else $out="<tr><td>There is no charge for this extension for this chargeclass</td></tr>";


	return $out;
}
?>