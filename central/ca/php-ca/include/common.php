<?

	function printHeaderbar() {
		print <<<ENDE
<div id=headerbar>
<div id=header>MedCommons-CA</div>
<div class=menu><a href="index.php?area=main&stage=help">Help</a></div>
<div class=menu><a href="index.php?area=main&stage=about">About</a></div>
<div class=menu><a href="index.php">Main page</a></div>
</div>
ENDE;
	}

	function printHeader($title = "") {
		print <<<ENDE
<html>
<head>
<title>MedCommons-CA: $title</title>
<script type="text/javascript" src="/ca/php-ca/include/common.php"></script>
<!--- <link rel="stylesheet" type="text/css" href="css/basic.php"/>--->
</head>
<body>
ENDE;
//		printHeaderbar();
	}

	function printFooter() {
		print <<<ENDE
</body>
</html>
ENDE;
	}

?>
