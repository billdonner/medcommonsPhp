
<html>
<head>
<title>MedCommons-CA: Key Generation</title>
<link rel="stylesheet" type="text/css" href="/ca/php-ca/css/basic.php"/>
</head>
<body>
<fieldset>
                <legend>Your Certificate Details</legend>
                <table>
                <colgroup><col width="180px">Hello, <b><?=$_SERVER['SSL_CLIENT_S_DN_CN']?></b> of <B><?=$_SERVER['SSL_CLIENT_S_DN_O']?></b>. Your role is <b><?=$_SERVER['SSL_CLIENT_S_DN_OU']?></B> and we will contact you at:: <b><?=$_SERVER['SSL_CLIENT_S_DN_Email']?></b></colgroup>
                <tr><th>Your full name (CN)</th><td><?=$_SERVER['SSL_CLIENT_S_DN_CN']?></td></tr>
                <tr><th>Your email Address</th><td><?=$_SERVER['SSL_CLIENT_S_DN_Email']?></td></tr>
                <tr><th>Portal Name (O)</th><td><?=$_SERVER['SSL_CLIENT_S_DN_O']?></td></tr>

                <tr><th>Role Name (OU)</th><td><?=$_SERVER['SSL_CLIENT_S_DN_OU']?></td></tr>


                </table>


        </fieldset>
</body>
</html>
<?php

  foreach ($_SERVER as $key => $value) {
   print $key."=>".$value."<br>";
  }
?>
