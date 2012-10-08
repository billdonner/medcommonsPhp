<?
$guessLocations = array(
                        '/usr/bin/openssl',
                        '/usr/local/bin/openssl',
                        '/usr/local/openssl/bin/openssl',
                        'c:/program files/openssl/bin/openssl',
                        'c:/openssl/bin/openssl'
                );

                $cmdSSL = '';
                foreach ($guessLocations as $location) {
                        if (file_exists($location)) {
                                $cmdSSL = $location;
                                break;
                        }
                }

                if ($cmdSSL) {
                        // Wow, we have it installed...
			$opensslconf ="/home/apache/htdocs/ca/php-ca/openssl/openssl.conf";
			$certdir ="/home/apache/htdocs/ca/php-ca/openssl/crypto/certs";
			$crlfile ="/home/apache/htdocs/ca/php-ca/openssl/crypto/crls/medcomm-ca.crl";		

                        $revokecommand = "$cmdSSL  ca -passin pass:asdfg -config $opensslconf -revoke ${certdir}/${serial}.pem";
                        $updatecrl = "$cmdSSL ca -passin pass:asdfg -gencrl -config $opensslconf -out $crlfile";
                        exec($revokecommand);
                        exec($updatecrl);




                }
@ $db = mysql_pconnect('localhost', 'admin', 'yEF2sSUE', 'ca');
mysql_select_db("ca");
if (!$db)
{
echo "Error: Could Not connect to DB";
exit;
} else {

$update= "UPDATE certificates SET status = 'revoked' WHERE serial like '".$serial."%'";

$results = mysql_query($update);

}
?>
