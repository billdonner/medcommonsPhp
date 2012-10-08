<?php
 
  require_once 'render.inc.php'; 

  global $acAmazonRedirectorUrl;

  // ssadedin: when invoked from gateway will get voucher details
  // passed which need to be forwarded to registration so that
  // it links them up at the end
  $randomappliance = select_random_appliance();
  $registerurl = "$randomappliance/acct/register.php";
  $vars = array("registerurl" => $registerurl, "next" => "", "devpayredir" => $acAmazonRedirectorUrl);
  $vars['purchasebuttonclass'] = $GLOBALS['purchase_disabled']?'disabledbutton':'primebutton';
  if(isset($_GET['src']) && isset($_GET['tid']) && isset($_GET['otp']) && isset($_GET['srcauth'])) {
    $src = $_GET['src'];

    // Break out the appliance name from the src url
    if(preg_match(",(http.*/)([0-9]{16}),i",$src, $match)!=1) {
      throw new Exception("Provided HealthURL is not in expected format.");
    }
    $srcHost = rtrim($match[1],'/');

    $srcauth = $_GET['srcauth'];
    $tid = $_GET['tid'];
    $otp = $_GET['otp'];
    $registerurl = "$srcHost/mod/payment_processed.php?copy=true&"."c=".urlencode($tid)."&o=".urlencode($otp)."&vcopy"; 
    $vars["registerurl"] = $registerurl;

    // We have to set up a complicated triple forward - first we go through login,
    // then to acct/gwredir, which sends us to our final destination, the import page
    $importUrl = "AccountImport.action?sourceUrl=".urlencode($src)."&sourceAuth=".urlencode($srcauth)."&auto=true";
    $url = "gwredir.php?dest=".urlencode($importUrl);
    $vars['next'] = "<input type='hidden' name='next' value='$url'/>";

    // echo "using register url = ".$registerurl;
  }
  renderas_webpage(false,$vars); 
?>
