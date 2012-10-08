<?

// should be deployed to facebook/common for now


  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  require "setup.inc.php";

  $ak = htmlspecialchars($_REQUEST['ActivationKey']);
  $pc = htmlspecialchars($_REQUEST['ProductCode']);
  
// do some housekeeping here, and then add the counters
  $src = isset($_COOKIE['src']) ? $_COOKIE['src'] : null;
   $btk = isset($_COOKIE['btk']) ? $_COOKIE['btk'] : null;

  if($src)
    $returnUrl = $src."?btk=$btk&ak=$ak&pc=$pc";
  else
    $returnUrl = $GLOBALS['Master_FBAPPURL']."index.php?paid_newacct=true";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
        <title>Thank you for Purchasing a MedCommons Personal Account!</title>
        <link rel='stylesheet' type='text/css' href='http://www.medcommons.net/medCommonsStyles.css'/>
</head>
<body>
        <h2>Thank you for Purchasing a MedCommons Personal  Account</h2>
	<p> Returnurl  <?=$returnUrl?>  Btk <?=$btk?> </p>
        <p>Please click the button below to connect your 
           purchased storage to your account.</p>

        <form name='fbreturn' method='post' action='<?=$returnUrl?>' target="_top">
          <input type='hidden' name='ActivationKey' value='<?=$ak?>'/>
          <input type='hidden' name='ProductCode' value='<?=$pc?>'/>
          <input type='submit' value='Return to your account' />
        </form>
</body>
</html>
