<?php
require_once "is.inc.php";


//$sql = "Select c.ind, c.playerind, c.parentind, c.playerind, tp.teamind, p.name, p.team from $cases c,teamplayers tp,leagueteams lt, players p where c.playerind=tp.playerind 
$result = dosql("Select * from islog order by ind desc limit 50 ");
$count = mysql_num_rows($result);
$ob="<h4>Last $count rows from IS Log, most recent on top</h4><table>";
while ($r=mysql_fetch_object($result))
{
	$pt = strftime('%T',$r->time);
	$url = substr($r->url,0,100);
	if (strlen($url) == 100) $url .='...';
	$ob .= "<tr><td>$pt</td><td>$r->type</td><td title='$r->ip' >$r->id</td><td>$url</td></tr>";
}
$ob.="</table>";
echo $ob;

exit;
?>