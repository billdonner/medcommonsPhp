<?

  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  require "setup.inc.php";

  $ak = $_REQUEST['ActivationKey'];
  $pc = $_REQUEST['ProductCode'];
  $src = isset($_COOKIE['src']) ? $_COOKIE['src'] : null;
  $returnUrl = $src;
  if(!$returnUrl)
    die("Expected cookie 'src' not found or blank");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
        <title>Thank you for Purchasing a MedCommons Personal Account!</title>
        <link rel='stylesheet' type='text/css' href='http://www.medcommons.net/css/medCommonsStyles.css'/>
</head>
<body>
<div id="ContentBoxInterior" mainId='page_signin' mainTitle="return to register">

        <h2>Thank you for Purchasing a MedCommons Account</h2>
	
        <p>Please click the button below to connect your 
           purchased storage to your account.</p>

        <form name='fbreturn' method='post' action='<?=$returnUrl?>' target="_top">
          <input type='hidden' name='ActivationKey' value='<?=htmlentities($ak)?>'/>
          <input type='hidden' name='ProductCode' value='<?=htmlentities($pc)?>'/>
          <input type='submit'  class='mainlarge smalltext' value='Return to your account  ' />
        </form>
</body>
</html>
