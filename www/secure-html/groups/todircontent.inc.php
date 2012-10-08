<?php
$odd = false;

$select=
"SELECT * FROM todir $whereclause ORDER BY time DESC LIMIT $limit";

$content = "<h3>toDir Query results</h3>";
$result = mysql_query($select) or die("can not select from  table todir - $select".mysql_error());
$rows = mysql_numrows($result);
if ($rows == 0)
$content .= "<p>No Rows Match</b>";
else
{//rows
$content.="<table cellspacing='0' summary='$mb'>
	<thead>
      <tr>
		<th title='competely arbitrary'>external id</th>
	  	<th title='when entry made'>time</th>
        <th title='competely arbitrary'>practice group</th> 
		<th title='normally an email address'>alias</th>
		<th title='administrator mcid'>medcommons id</th>
		<th title='this should be cdata'>xhtml contact info</th>
		</tr>
	</thead>";
	

while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$odd = (!$odd); // flip polarity
	//guid is a link
	$lxid = $l['xid'];

	$ct = $l['time'];
	$time = strftime('%T',$ct);
	$date = strftime('%D',$ct);
		$rowclass = ($odd?"odd":"even");
	$lctx = $l['ctx'];
	$lalias = $l['alias'];
	$laccid = $l['accid'];
	$lcontact = $l['contact'];

	$content.="<tr class='$rowclass'>";
	$content .= "<td >".$lxid."</td>";

	$content .= "<td >".$time." ".$date."</td>";
	$content .= "<td >".$lctx."</td>";
	$content .= "<td >".$lalias."</td>";
	$content .= "<td >".$laccid."</td>";
	$content .= "<td ><xmp>".$lcontact."</xmp></td>";
	$content.="</tr>";
}
$content .= "</table>";
}
?>