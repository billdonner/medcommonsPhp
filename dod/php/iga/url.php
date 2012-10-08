<?php

require 'urls.inc.php';

$VERIFY_SECRET = 'HMAC secret used to verify email addresses';

if (isset($_GET['mcid'])) {
  $mcid = $_GET['mcid'];
  $hmac = hash_hmac('SHA1', $mcid, $VERIFY_SECRET);
  $url = $GLOBALS['Accounts_Url'] . 'verify.php?mcid=' . $mcid . '&hmac=' .
    $hmac;
}
 else
   $mcid = '';

?><html>
  <head>
    <title>Generate new S/Key Receipt</title>
  </head>
  <body>
    <form method='get' action='url.php'>
      <input type='text' name='mcid' value='<?php echo $mcid; ?>' />
      <inpyt type='submit' />
    </form>

<?php
if (isset($_GET['mcid'])) {
?>
  <a href='<?php echo $url; ?>'><?php echo $url; ?></a>
<?php
}
?>
  </body>
</html>

