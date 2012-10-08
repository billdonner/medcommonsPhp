<?

$domain = preg_replace('/[^@]+@/','', $config['contact']);

?>
<table border="0"> <tbody><tr><td><img src="images/MEDcommons_logo_246x50_002.gif" alt="medcommons, inc." height="50" width="246"></td>
<td><h4>Generating a Certificate for <?=$t?></h4></td><td><small><a href="/ca/php-ca/start.htm">ca home</a></small></td></tr></tbody></table><br>
<p>

</p><p>
Please enter the email address for the party you wish to generate a cert for <?=$t?>.
</p>

<p>
Warning: This party is presumed to be associated with <?=$t?>. Once
the certificate is given to this party, she will have access to
resources associated with <?=$t?>.
</p>

<p>
That party will receive an email with a secret message inside it.
</p>

<p>They will need to click on a link in the email and enter the secret message into MedCommons. 
</p>



<form method="post" action="index.php">
<input type="hidden" name="area" value="apply">
<input type="hidden" name="stage" value="enterKey">
<input type="hidden" name="o" value="<?=$o?>">
<input type="hidden" name="t" value="<?=$t?>">
        <fieldset style="width: 400px;">
                <legend>Email address validation</legend>
                <p>
                Please enter your email address in the following box:
                </p>

                <table>
                <colgroup><col width="180px"></colgroup>
                <tr><th>Email Address</th><td><input type="text" name="emailAddress" value="me@<?=htmlspecialchars($domain)?>" size="30"></td></tr>
                <tr><td colspan=2 style="text-align: right;"><input type="submit" value="Make a <?=$t?> Cert"></td></tr>
                </table>
        </fieldset>
</form>



<h3>Notes</h3>

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



