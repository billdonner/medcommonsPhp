<?php
/**
 * Enter description here...
 *
 * @return unknown
 */
function showbill($accid)
{
	$out='';

	$db = aconnect_db(); // connect to the right database

	$query = "SELECT * from appeventlog e, appservices a, appservicechargeclasses c
where e.accid='$accid' and e.appserviceid=a.appserviceid and c.appserviceid=a.appserviceid and
c.chargeclass = e.chargeclass order by  e.time ";

	$result = mysql_query ($query) or die("can not query table appseventlog - ".mysql_error());
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
			// figure out the cost
			$w = explode(',',$a['param1']);
			$cost = $w[0]+$a['setup']*$w[1]+$a['permonth']*$w[2]+$a['perclick']*$w[3]+$a['perxmtgb']*$w[4]
			+$a['perrcvgb']*$w[5]+$a['perstoredgb']*$w[6];
			//	if ($cost != 0)
			{
				$balance += $cost;
				$totalcost += intval($cost);
				$rowclass = ($odd?"odd":"even");
				$out.="<tr class='$rowclass'><td>".$a['name']."</td><td>$time $date</td><td>".
				$a['eventname']."</td>".
				"<td>".$a['chargeclass']."</td>".
				"<td>".$w[1]*$a['setup']."</td>".
				"<td>".$w[2]*$a['permonth']."</td>".
				"<td>".$w[3]*$a['perclick']."</td>".
				"<td>".$w[4]*$a['perxmtgb']."</td>".
				"<td>".$w[5]*$a['perrcvgb']."</td>".
				"<td>".$w[6]*$a['perstoredgb']."</td>".
				"<td>".$cost."</td>".
				"<td>".$balance."</td>".
				"</tr>";
			}
		}
		$out.="</table>";
		if ($balance>0)
		$billpay =<<<XXX
	<a  onclick="return paymentpopup('../pay/payviacc.php?price=$balance')" 
	      href='../pay/payviacc.php?price=$balance' >pay bill</a>&nbsp
XXX;


		else $billpay = '';
		$begin="<small>$billpay <a href=clearappevents.php>reset</a></small><p><table class='trackertable'>
                <tr><th>service</th><th>time</th><th>event</th><th>charge class</th>
				<th>setup</th><th>per month</th><th>per click</th><th>per xmtgb</th>
<th>per rcvgb</th><th>per storedgb</th><th>cost</th><th>balance</th>
</tr>";
		return array($balance,$begin.$out);
	}
	else return false;


}
?>