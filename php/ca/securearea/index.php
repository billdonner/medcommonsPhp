<html>
<head>
<title>MedCommons-CA: Secured by Cerificates</title>
<!--- <link rel="stylesheet" type="text/css" href="css/basic.php"/>--->
</head>
<body>
<table border="0"> <tbody><tr><td><img src="/ca/php-ca/images/MEDcommons_logo_246x50_002.gif" alt="medcommons, inc." height="50" width="246"></td>
<td><h4>Secure Area</h4></td><td><small><a href="/ca/php-ca/start.htm">ca home</a></small></td></tr></tbody></table><br>
<fieldset>
                <legend>Your Certificate Details</legend>
                <table>
                <colgroup><col width="180px">Hello, <b><?=$_SERVER['SSL_CLIENT_S_DN_CN']?></b> of <B><?=$_SERVER['SSL_CLIENT_S_DN_O']?></b>. Your role is <b><?=$_SERVER['SSL_CLIENT_S_DN_OU']?></B> and we will contact you at:: <b><?=$_SERVER['SSL_CLIENT_S_DN_Email']?></b></colgroup>
                <tr><th>Your full name (CN)</th><td><?=$_SERVER['SSL_CLIENT_S_DN_CN']?></td></tr>
                <tr><th>Your email Address</th><td><?=$_SERVER['SSL_CLIENT_S_DN_Email']?></td></tr>
                <tr><th>Oraganization Name (O)</th><td><?=$_SERVER['SSL_CLIENT_S_DN_O']?></td></tr>

                <tr><th>Role Name (OU)</th><td><?=$_SERVER['SSL_CLIENT_S_DN_OU']?></td></tr>


                </table>


        </fieldset>


<h3>Notes</h3>

<div id="footer"><small>This show the details of the certificate</small></div>
<p>
<table><tbody><tr>
<td><img src="/ca/php-ca/images/MEDcommons_logo_246x50.gif"></td>
<td><img src="/ca/php-ca/images/diag_astmlogo.gif"></td>
<td><img src="/ca/php-ca/images/PingFederate%2520Logo.gif"></td>
<td><img src="/ca/php-ca/images/verisignimage.jpg"></td>
<td><img src="/ca/php-ca/images/identrus.jpg"></td>
</tr>

</tbody></table>
</p>


<?php
// update certificate db with lastaccess
include("/home/ops/htdocs/ca/php-ca/include/lastaccess.php");
        if ($_REQUEST['debug']) {
print "<div id=footer><small>Full SSL/TLS Session Variables available for further authorization logic: <P>";
 foreach ($_SERVER as $key => $value) {
 if (preg_match("/SSL/", "$key")) {
 print $key."=>".$value."<br>";
}
 }
}
?>

</small></div>
</body>
</html>
