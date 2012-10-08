
<?

$emailAddress = $_SERVER['SSL_CLIENT_S_DN_Email'];
$serialnum = $_SERVER['SSL_CLIENT_M_SERIAL'];


@ $db = mysql_pconnect('localhost', 'admin', 'yEF2sSUE', 'ca');
mysql_select_db("ca_ops");
if (!$db)
{
echo "Error: Could Not connect to DB";
exit;
} else {


$update = "update certificates set lastaccess = SYSDATE() where serial = ".$serialnum." and email = '".$emailAddress."'";

$results = mysql_query($update);

}
?>
