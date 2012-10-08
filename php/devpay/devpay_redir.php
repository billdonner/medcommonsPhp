<?
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  error_log('setting cookie for source url '.$_REQUEST['src']);
  setcookie('src',$_REQUEST['src'], time()+3600);

  // The old test product - no longer used
  // header("Location: https://aws-portal.amazon.com/gp/aws/user/subscription/index.html?offeringCode=906A40F0");

  // New (real) product
  header("Location: https://aws-portal.amazon.com/gp/aws/user/subscription/index.html?offeringCode=68A808C7");
?>
