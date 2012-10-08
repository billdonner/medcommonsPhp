<?

	if (get_magic_quotes_gpc()) {
		$emailAddress = stripslashes($_REQUEST['emailAddress']);
		$secret = stripslashes($_REQUEST['secret']);
	}
	else {
		$emailAddress = &$_REQUEST['emailAddress'];
		$secret = &$_REQUEST['secret'];
	}

	if (!$_REQUEST['emailAddress']) {
		print "Your email address was not found in the input.<br/>\n";
		print "--&gt; <a href=\"index.php?area=apply\">Try again</a><br/>\n";
	}

	elseif (!$_REQUEST['secret']) {
		print "Your secret was not found in the input.<br/>\n";
		print "--&gt; <a href=\"index.php?area=apply&stage=enterKey&sent=1&emailAddress=".urlencode($emailAddress)."\">Try again</a><br/>\n";
	}

	elseif (md5($config['passPhrase'] . $emailAddress) != $secret) {
		print "Your secret does not seem to be correct. Make sure it has no spaces and is exactly as it appears in your email.<br/>\n";
		print "--&gt; <a href=\"index.php?area=apply&stage=enterKey&sent=1&emailAddress=".urlencode($emailAddress)."\">Try again</a><br/>\n";
	}

	else {

?>
<html>
<head>
<title>MedCommons-CA: Key Generation</title>
<!-- Use the Microsoft ActiveX control to generate the certificate -->
<object classid="clsid:127698e4-e730-4e5c-a2b1-21490a70c8a1" codebase="/certcontrol/xenroll.dll" id="certHelper">
</object>
<script type="text/javascript">
<!--
var ie = (document.all && document.getElementById);
var ns = (!document.all && document.getElementById);

function GenReq()
{
    var szName          	= "";
	var objID				= "1.3.6.1.4.1.311.2.1.21";

    szName = "";
    
    if (document.GenReqForm.emailAddress.value == "") {
		alert("No email Address");
		return false;
    } 
    else
		szName = "E=" + document.GenReqForm.emailAddress.value;

    if (document.GenReqForm.commonName.value == "") {
		alert("No Common Name");
		return false;
    } 
    else
		szName = szName + ", CN=" + document.GenReqForm.commonName.value;

    if (document.GenReqForm.countryName.value == "") {
		alert("No Country");
		return false;
    }
    else
		szName = szName + ", C=" + document.GenReqForm.countryName.value;

    if (document.GenReqForm.stateOrProvinceName.value == "") {
		alert("No State or Province");
		return false;
    }
    else
		szName = szName + ", S=" + document.GenReqForm.stateOrProvinceName.value;

    if (document.GenReqForm.localityName.value == "") {
		alert("No City");
		return false;
    }
    else
		szName = szName + ", L=" + document.GenReqForm.localityName.value;

    if (document.GenReqForm.organizationName.value == "") {
		alert("No Organization");
		return false;
    }
    else
		szName = szName + ", O=" + document.GenReqForm.organizationName.value;

    if (document.GenReqForm.organizationalUnitName.value == "") {
		alert("No Organizational Unit");
		return false;
    }
    else
		szName = szName + ", OU=" + document.GenReqForm.organizationalUnitName.value;
		

	if (!ie) return true;

    certHelper.KeySpec = 1;
    certHelper.GenKeyFlags =0x04000003;
	certHelper.ProviderName="";

	//alert (szName);
	//alert (objID);

	try {
	    sz10 = certHelper.CreatePKCS10(szName, objID);
	}
	catch (e) {
		alert ("Error generating request");
		return false;
	}

    if (sz10 != "") {
		document.GenReqForm.reqEntry.value = sz10;
    }
	else {
		alert("Key Pair Generation failed");
		return false;
    }
}

//-->
</script>
<link rel="stylesheet" type="text/css" href="/ca/php-ca/css/basic.php"/>
</head>
<body>

<table border="0"> <tbody><tr><td><img src="images/MEDcommons_logo_246x50_002.gif" alt="medcommons, inc." height="50" width="246"></td>
<td><h4>Enter Certificate Details for ops-team</h4></td><td><small><a href="/ca/php-ca/start.htm">ca home</a></small></td></tr></tbody></table><br>
<p>

</p><p>
This is the penultimate step towards getting your certificate 
<br>Please fill in the following form which contains the basic information for your certificate. 
</p>

<form method="post" action="index.php" name="GenReqForm" onSubmit="return GenReq();">
<input type="hidden" name="area" value="apply">
<input type="hidden" name="stage" value="signCert">
<input type="hidden" name="secret" value="<?=htmlspecialchars($secret)?>">
	<fieldset>
		<legend>Information about you</legend>
		<p>
 Please enter all requested information in yellow.
		</p>

		<p>
		This information will be displayed inside the certificate that will be issued to you.
		</p>
		<table>
		<colgroup><col width="180px"></colgroup>
		<tr><th>Your full name (CN)</th><td><input type="text" id="commonName" name="dn[commonName]" value="YOUR NAME HERE" size="40" style="background:#FFFF99"></td></tr>
		<tr><th>City</th><td><input type="text" id="localityName" name="dn[localityName]" value="ENTER YOUR CITY" size="25" style="background:#FFFF99"></td></tr>
		<tr><th>State</th><td><input type="text" id="stateOrProvinceName" name="dn[stateOrProvinceName]" value="ENTER YOUR STATE" size="25" style="background:#FFFF99"></td></tr>
		<tr><th>Country</th><td><input type="text" id="countryName" name="dn[countryName]" value="<?=htmlspecialchars($config['country'])?>" size="2" style="background:#FFFF99"></td></tr>
		<tr><th>Your email Address</th><td><input type="text" id="emailAddress" name="dn[emailAddress]" value="<?=htmlspecialchars($emailAddress)?>" size="30" readonly></td></tr>
		<tr><th>Organization Name (O)</th><td><input type="text" id="organizationName" name="dn[organizationName]" value="<?=$o?>" size="25" readonly></td></tr>
		<tr><th>Organizational Unit (OU)</th><td><input type="text" id="organizationalUnitName" name="dn[organizationalUnitName]" value="<?=$t?>" size="30" readonly></td></tr>
<script type="text/javascript">
if (!ns)
	document.write("<!"+"--");
</script>
		</table>
		
		<p>
		Please choose a cypher strength (we recommend the highest possible value available)
		</p>

		<table>
		<colgroup><col width="180px"></colgroup>
		<tr><th>Strength</th><td><keygen name="SPKAC" challenge="challengePassword"></td></tr>
<!-- end Netscape specific segment -->

		<tr><td colspan=2 style="text-align: right;"><input type="submit" value="Create Certificate"></td></tr>
		</table>
	</fieldset>
<input type="hidden" name="reqEntry">
</form>
<h3>Notes</h3>

<div id="footer"><small>Please be patient.</small></div>
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
