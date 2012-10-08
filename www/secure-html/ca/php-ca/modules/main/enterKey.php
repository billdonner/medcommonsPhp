<?

	if (!$_REQUEST['emailAddress']) {
		print "Your email address was not found in the input.<br/>\n";
		print "--&gt; <a href=\"index.php?area=apply\">Try again</a><br/>\n";
	}

	else {

		print "<h1>Email address verification</h1>\n";

		if (!$_REQUEST['sent']) {
			//print "<b>Sending the secret to your email address...</b><br/>";
			flush();

			if (get_magic_quotes_gpc()) {
				$emailAddress = stripslashes($_REQUEST['emailAddress']);
			}
			else {
				$emailAddress = &$_REQUEST['emailAddress'];
			}

			$secretMessage = md5($config['passPhrase'] . $emailAddress);
			$urlEmail = urlencode($emailAddress);
			$requestURI = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
			$message = <<<ENDE

Greetings from  the {$config["orgName"]} Certificate Authority. You have been invited to join our secure operations network running at https://ops.medcommons.net

If you do not complete this process, you will be unable to access https://ops.medcommons.net

Here is your secret:
	$secretMessage

You can click this URL to go back there:
https://{$_SERVER['HTTP_HOST']}{$requestURI}?area=apply&stage=enterKey&sent=1&emailAddress=$urlEmail

Regards,

{$config["orgName"]} Certificate Authority.
mailto:{$config["contact"]}
ENDE;

			mail($emailAddress, "Your secret from the {$config["orgName"]} CA", $message);

			//print "Done<br/><br/>\n";
		}
?>

<p>
Once you have recieved your secret, you can enter it in the following box.
</p>

<form method="post" action="index.php">
<input type="hidden" name="area" value="apply">
<input type="hidden" name="stage" value="issueCert">
<input type="hidden" name="emailAddress" value="<?=htmlspecialchars($emailAddress)?>">
	<fieldset style="width: 400px;">
		<legend>Email address validation</legend>
		<p>
		Please enter the secret:
		</p>

		<table>
		<colgroup><col width="180px"></colgroup>
		<tr><th>Secret</th><td><input type="text" name="secret" value="" size="40"></td></tr>
		<tr><td colspan=2 style="text-align: right;"><input type="submit" value="Check the secret"></td></tr>
		</table>
	</fieldset>
</form>

<?

	}

?>
