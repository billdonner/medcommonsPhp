<?

//print "Install cert:<br/><br/>"; flush();
	if (get_magic_quotes_gpc()) {
		$id = stripslashes($_REQUEST['id']);
	}
	else {
		$id = $_REQUEST['id'];
	}

	$certFile = "./openssl/crypto/certs/${id}.pem";
	if (file_exists($certFile)) {
		$fp = fopen($certFile, 'r');
		$myCert = fread($fp, filesize($certFile));
	}
	else {
		printHeader("Certificate Retrieval");
		print "<h1>X509 user certificate not found</h1> $id";
		printFooter();
	}

?>
		<a href=<?=$certFile?>>Install Certificate</a><BR>
