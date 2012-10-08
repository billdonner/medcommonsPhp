<?
if ($_REQUEST['revoke'] == 'true') {
$serial = $_REQUEST['serial'];
include("revokeCert.php");
}
@ $db = mysql_pconnect('localhost', 'admin', 'yEF2sSUE', 'ca');
mysql_select_db("ca");
if (!$db)
{
echo "Error: Could Not connect to DB";
exit;
} else {

$query= "select * from certificates";
$result = mysql_query($query);

}


?>

<table border="0"> <tbody><tr><td><img src="images/MEDcommons_logo_246x50_002.gif" alt="medcommons, inc." height="50" width="246"></td>
<td><h4>Certificate Administration</h4></td><td><small><a href="/ca/php-ca/start.htm">ca home</a></small></td></tr></tbody></table><br>


<p>
Here are the currently issued certificates
</p>


        <fieldset style="width: 95%;">
                <legend>Certificate Administration</legend>
<?
echo "<table border='1' cellspacing='0' cellpadding='0' width=100%>";
echo "<tr> <th>MedCommons ID</th> <th>Common Name (CN)</th> <th>Email</th><th>Organization (O)</th><th>Organizational Unit (OU)</th><th>City</th><th>State</th><th>Country</th><th>Serial Num</th><th>Status</TH><th>Last Access</TH><TH>Task</TH></tr>";
// keeps getting the next row until there are no more to get
while($row = mysql_fetch_array( $result )) {
// Print out the contents of each row into a table
echo "<tr><td>";
echo $row['id'];
echo "</td><td>";
echo $row['cn'];
echo "</td><td>";
echo $row['email'];
echo "</td><td>";
echo $row['o'];
echo "</td><td>";
echo $row['ou'];
echo "</td><td>";
echo $row['city'];
echo "</td><td>";
echo $row['state'];
echo "</td><td>";
echo $row['country'];
echo "</td><td>";
echo $row['serial'];
echo "</td><td>";
echo $row['status'];
echo "</td><td>";
echo $row['lastaccess'];
echo "</td><td>";
if ( $row['status'] == 'active') {
echo "<a href=index.php?area=apply&stage=list&revoke=true&serial=".$row['serial'].">Revoke</a>";
}
echo "</td></tr>";
}
echo "</table>";
?>
        </fieldset>

<h3>Notes</h3>

<div id="footer"><small></small></div>
<p>
<table><tbody><tr>
<td><img src="images/MEDcommons_logo_246x50.gif"></td>
<td><img src="images/diag_astmlogo.gif"></td>
<td><img src="images/PingFederate%2520Logo.gif"></td>
<td><img src="images/verisignimage.jpg"></td>
<td><img src="images/identrus.jpg"></td>
</tr>
</tbody></table>
</p>
