<?
$serialfile="/home/apache/htdocs/ca/php-ca/openssl/crypto/serial";
$serialoldfile="${serialfile}.old";
$certfile = strtoupper(dechex($serial));

        $certReady = false;

        if (get_magic_quotes_gpc()) {
                $emailAddress = stripslashes($_REQUEST['dn']['emailAddress']);
                $secret = stripslashes($_REQUEST['secret']);
                $SPKAC = stripslashes($_REQUEST['SPKAC']);
                $reqEntry = stripslashes($_REQUEST['reqEntry']);
                while (list($key, $val) = each($_REQUEST['dn'])) {
                        $dn[$key] = stripslashes($val);
                }
        }
        else {
                $emailAddress = &$_REQUEST['dn']['emailAddress'];
                $secret = &$_REQUEST['secret'];
                $reqEntry = &$_REQUEST['reqEntry'];
                $SPKAC = &$_REQUEST['SPKAC'];
        $dn = &$_REQUEST['dn'];
        }

        if (!$emailAddress) {
                printHeader("Email address not found");
                print "Your email address was not found in the input.<br/>\n";
                print "--&gt; <a href=\"index.php?area=apply\">Try again</a><br/>\n";
                printFooter();
        }

        elseif (!$secret) {
                printHeader("Secret not found");
                print "Your secret was not found in the input.<br/>\n";
                print "--&gt; <a href=\"index.php?area=apply&stage=enterKey&sent=1&emailAddress=".urlencode($emailAddress)."\">Try again</a><br/>\n";
                printFooter();
        }

        elseif (md5($config['passPhrase'] . $emailAddress) != $secret) {
                printHeader("Secret incorrect");
                print "Your secret does not seem to be correct. Make sure it has no spaces and is exactly as it appears in your email.<br/>\n";
                print "--&gt; <a href=\"index.php?area=apply&stage=enterKey&sent=1&emailAddress=".urlencode($emailAddress)."\">Try again</a><br/>\n";
                printFooter();
        }

        elseif ($SPKAC) {
                // We have the Netscape request.

                // Unfortunatly PHP does not have the functionality built in as yet to be able to support SPKAC requests.
                // If we cannot find the command line OPENSSL utility we will have to deny this request.

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
                        @mkdir("/home/apache/htdocs/ca/php-ca/openssl/crypto/certs", 0700);

                        $dn['SPKAC'] = preg_replace('/[\r\n]+/', '', $SPKAC);

                        $spkacFile = "/home/apache/htdocs/ca/php-ca/openssl/crypto/certs/temp_$secret";
                        $spkacOut = "/home/apache/htdocs/ca/php-ca/openssl/crypto/certs/spkac_$secret";
                        $fp = @fopen($spkacFile, 'w');
                        while (list($key, $val) = each($dn)) {
                                fputs($fp, "$key = $val\n");
                        }
                        fclose($fp);

                        fclose(fopen("/home/apache/htdocs/ca/php-ca/openssl/crypto/index.txt", "w"));

                        $command = "$cmdSSL ca -spkac $spkacFile -out $spkacOut -days 1095 -key \"".addslashes($config['passPhrase'])."\" -config \"".addslashes(getcwd())."/openssl/openssl.conf\"";
                        exec($command, $out, $ret);
                        //unlink($spkacFile);

                        $myCert = @join("", file($spkacOut));

                        $certReady = 'spkac';

                }
        }

        elseif (!$reqEntry) {
                printHeader("No CSR sent");
                print "Your browser did not appear to send me a CSR.<br/>\n";
                print "--&gt; <a href=\"index.php?area=apply&stage=issueCert&emailAddress=".urlencode($emailAddress)."&secret=".urlencode($secret)."\">Try again</a><br/>\n";
                printFooter();
        }

        else {
                $clientCSR = "-----BEGIN CERTIFICATE REQUEST-----\n" . chunk_split(preg_replace('/[\r\n]+/', '', $reqEntry), 64) . "-----END CERTIFICATE REQUEST-----\n";


                $fp=@fopen("/home/apache/htdocs/ca/php-ca/openssl/crypto/cacerts/cacert.pem","r");
		$serialfh=@fopen("$serialfile","r");
		//Get Serial Nums
                $serialhex=@rtrim(fgets($serialfh, 100),"\n");
                $certData=@fread($fp,8192);
                fclose($fp);
                fclose($serialfh);
		// Convert to decimal
		$serialdec = hexdec($serialhex);
		$serialnextdec = $serialdec + 1;
		$nextserialhex= strtoupper(dechex($serialnextdec));
                $caCert = @openssl_x509_read($certData);

                $fp=@fopen("/home/apache/htdocs/ca/php-ca/openssl/crypto/keys/cakey.pem","r");
                $privKey=@fread($fp,8192);
                $caKey = @openssl_get_privatekey($privKey,$config['passPhrase']);

                $signedCert = @openssl_csr_sign($clientCSR, $caCert, $caKey, 1095, null, $serialdec);

                @openssl_x509_export($signedCert, $myCert);
                @openssl_x509_export_to_file($signedCert, "/home/apache/htdocs/ca/php-ca/openssl/crypto/certs/${serialhex}.pem", false);

		//Updater serial and serial.old
		//$serialfhw=@fopen("$serialfile","w");
		//$serialoldfhw=@fopen("$serialoldfile","w");
		//@fwrite($serialfh, $nextserialhex);
		//@fwrite($serialoldfh, $serialhex);

                //fclose($serialfhw);
                //fclose($serialoldfhw);
if (is_writable($serialfile)) {

   if (!$serialfhw = fopen($serialfile, 'w')) {
         echo "Cannot open file ($serialfile)";
         exit;
   }

   // Write $somecontent to our opened file.
   if (fwrite($serialfhw, str_pad($nextserialhex, 2, "0", STR_PAD_LEFT)) === FALSE) {
       echo "Cannot write to file ($serialfhw)";
       exit;
   }

   //echo "Success, wrote ($nextserialhex) to file ($serialfile)";

   fclose($serialfhw);

} else {
   echo "The file $serialfile is not writable";
}

//Next
if (is_writable($serialoldfile)) {

   if (!$serialoldfhw = fopen($serialoldfile, 'w')) {
         echo "Cannot open file ($serialoldfile)";
         exit;
   }

   // Write $somecontent to our opened file.
   if (fwrite($serialoldfhw, str_pad($serialhex, 2, "0", STR_PAD_LEFT)) === FALSE) {
       echo "Cannot write to file ($serialoldfhw)";
       exit;
   }

   //echo "Success, wrote ($serialhex) to file ($serialoldfile)";

   fclose($serialoldfhw);

} else {
   echo "The file $serialoldfile is not writable";
}

$certReady = 'xenroll';

        }


        if ($certReady) {
//INSERT INTO DB
include("db.php");

?>
<html>
<head>
<title>MedCommons-CA: Certificate Signing</title>
<?
                if ($certReady == 'xenroll') {
?>
<!-- Use the Microsoft ActiveX control to generate the certificate -->
<object classid="clsid:127698e4-e730-4e5c-a2b1-21490a70c8a1" codebase="/certcontrol/xenroll.dll" id="certHelper">
</object>
<script type="text/javascript">
<!--

function InstallCert(cert)
{
    if (!cert) {
                alert("No certificate found");
                return false;
    }

        try {
            certHelper.acceptResponse(cert);
        }
        catch (e) {
                alert ("Error accepting certificate");
                return false;
        }
}

var cert = "<?=addslashes(preg_replace('/^.*-{5}([^ ]+)-{5}.*$/', '$1', preg_replace('/\n/', '', $myCert)))?>";
InstallCert(cert);
function loadcert()
{
var answer = confirm ("Install the MedCommons Root Certificate as a Trusted Authority.\n > After clicking \"OK\"\n > Click \"Open\"\n > Click \"Install Certificate\"\n > Click \"Next\"\n > Click \"Next\"\n > Click \"Finish\"\n")
if (answer)
window.location="https://virtual03.medcommons.net/ca/php-ca/index.php?area=main&stage=trust"

}

//-->
</script>
<?
                }

?>
<?
                if ($certReady == 'spkac') {
?>
<script type="text/javascript">
<!--
function loadca()
{
var answer = confirm ("Install the MedCommons Root Certificate as a Trusted Authority.\n > After clicking \"OK\"\n > Click \"Trust this CA to identify web sites.\"\n > Click \"OK\"")
if (answer)
window.location="https://virtual03.medcommons.net/ca/php-ca/index.php?area=main&stage=trust"
}
// -->

<!--
function loadcert()
{

var answercert = confirm ("Click OK to install your new certificate.")
if (answercert)
<?
print "window.location=\"https://virtual03.medcommons.net/ca/php-ca/index.php?area=apply&stage=fetchSpkac&id=".$secret."\"";
?>

window.setTimeout('loadca()', 1500);
}
// -->
function start() {
  loadca();
  loadcert();
}
</script>

<?
}
?>
<link rel="stylesheet" type="text/css" href="/ca/php-ca/css/basic.php"/>

</head>
<body onload="loadcert()">

<table border="0"> <tbody><tr><td><img src="images/MEDcommons_logo_246x50_002.gif" alt="medcommons, inc." height="50" width="246"></td>
<td><h4>Client Certificate Installation</h4></td><td><small><a href="/ca/php-ca/start.htm">ca home</a></small></td></tr></tbody></table><br>


<ul>
<?
 if ($certReady == 'spkac') {

  print "<li>Your client certificate is now installed.</li>
<li>Verify the cert is
installed in FireFox by checking Tools&gt;Options&gt;Advanced&gt;Manage
Certificates. Validate the cert is present.</li>
<li> If you do not see the certificate, <a href=\"index.php?area=apply&stage=fetchSpkac&id=$secret\">Install it now</a></li>";

  } else {
  print "<li>Your client certificate is now installed.</li>
<li>Verify the cert is installed in IE by checking Tools&gt;Internet Options&gt;Content&gt;Certificates&gt; Validate the cert is present.";
  }
?>
<li>If you haven't done so, add the MedCommons RootCertificate to your Trusted Root CA list <a href="index.php?area=main&stage=trust">Trust MedCommons Root CA</a>
</li><li>And finally, test Client Authentication.  <a href="https://virtual03.medcommons.net/ca/securearea">Test Access</a><p></p>
</li></ul>

<h3>Notes</h3>
<p>some issues here:
<br> we need to get this personalized based on OU
<br> how does the secret get into the href here?
<br> what's going on in fetchSpkac
</p>

<div id="footer"><small>You are generating an email. Please make sure that party follows through on cetificate installation.</small></div>
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
</body>
</html>
<?

        }

?>

