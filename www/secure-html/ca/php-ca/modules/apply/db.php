
<?
$commonName = $_REQUEST['dn']['commonName'];
$emailAddress = $_REQUEST['dn']['emailAddress'];
$organizationName = $_REQUEST['dn']['organizationName'];
$organizationalUnitName = $_REQUEST['dn']['organizationalUnitName'];
$localityName = $_REQUEST['dn']['localityName'];
$stateOrProvinceName = $_REQUEST['dn']['stateOrProvinceName'];
$countryName = $_REQUEST['dn']['countryName'];

settype($template, "string");
// you could repeat the alphabet to get more randomness
$template = "1234567890";

function GetRandomString($length) {

       global $template;

       settype($length, "integer");
       settype($rndstring, "string");
       settype($a, "integer");
       settype($b, "integer");
      
       for ($a = 0; $a <= $length; $a++) {
               $b = rand(0, strlen($template) - 1);
               $rndstring .= $template[$b];
       }
      
       return $rndstring;
      
}

$filename="/home/apache/htdocs/ca/php-ca/openssl/crypto/serial.old";
$fpser=fopen("$filename","r");
                $serialnum=fread($fpser,filesize($filename));
                fclose($fpser);
$mcid=GetRandomString(16);
@ $db = mysql_pconnect('localhost', 'admin', 'yEF2sSUE', 'ca');
mysql_select_db("ca");
if (!$db)
{
echo "Error: Could Not connect to DB";
exit;
} else {

$inserts= "insert into certificates values 
(".$mcid.", '".$commonName."', '".$emailAddress."', '".$organizationName."', '".$organizationalUnitName."', '".$localityName."', '".$stateOrProvinceName."', '".$countryName."', '".$serialnum."','active')";

$results = mysql_query($inserts);

}
?>
