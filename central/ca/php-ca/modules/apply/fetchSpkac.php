<?

//print "Install cert:<br/><br/>"; flush();
	if (get_magic_quotes_gpc()) {
		$id = @stripslashes($_REQUEST['id']);
	}
	else {
		$id = &$_REQUEST['id'];
	}

	$spkacFile = "/home/apache/htdocs/ca/php-ca/openssl/crypto/certs/spkac_$id";
	if (file_exists($spkacFile)) {
		$fp = @fopen($spkacFile, 'r');
		$byteSize = @filesize($spkacFile);
		$myCert = @fread($fp, $byteSize);

		header("Content-Type: application/x-x509-user-cert");
		header("Content-Length: ".$byteSize);
		print $myCert;
	}
	else {
		printHeader("Certificate Retrieval");
		print "<h1>X509 user certificate not found</h1>\n";
		print "$spkacFile\n";
		printFooter();
	}

?>
