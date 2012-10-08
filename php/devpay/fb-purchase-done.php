<?
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  require "setup.inc.php";

  $src = isset($_COOKIE['src']) ? $_COOKIE['src'] : null;
  if($src)
    $returnUrl = $src;
  else
    $returnUrl = $GLOBALS['Master_FBAPPURL']."index.php?paid_newacct=true";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
        <title>Thank you for Purchasing a MedCommons Storage Account!</title>
        <link rel='stylesheet' type='text/css' href='http://www.medcommons.net/medCommonsStyles.css'/>
</head>
<body>
        <h2>Thank you for Purchasing a MedCommons Storage Account!</h2>

        <p>Please click the button below to return to Facebook and connect your 
           purchased storage to your Facebook account.</p>

        <form name='fbreturn' method='post' action='<?=$returnUrl?>' target="_top">
          <input type='hidden' name='ActivationKey' value='<?=htmlspecialchars($_REQUEST['ActivationKey'])?>'/>
          <input type='hidden' name='ProductCode' value='<?=htmlspecialchars($_REQUEST['ProductCode'])?>'/>
          <input type='submit' value='Return to Facebook ...'/>
        </form>
</body>
</html>
