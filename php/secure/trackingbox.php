<?PHP
//handles tracking box, might be ID
require "dbparams.inc.php";
require_once "track.inc.php";
require_once "session.inc.php";




$tracking = htmlentities($_REQUEST['trackingbox']);
$tracking = str_replace(array(' ','=','?',':','-'),"",$tracking);

if(is_numeric($tracking)==true) {

  // is it a medcommons id?

  tracking_process($tracking);

  if(strlen($tracking)<16) { // treat as tracking number
    // if here we have no match
    $url = $_REQUEST['returnurl'];
    header("Location: $url");
    echo $x;
    exit;
  }
}

// if already logged on then return back to website
if (isset($_COOKIE['mc'])) // wld lstrong checking
{
$mc = $_COOKIE['mc'];
if($mc !='') {
    $url = $_REQUEST['returnurl2'];
    $terryUrl = strong_url($url);
    $x="<html><head><title>error</title><meta http-equiv='REFRESH' content='0;url=".$terryUrl."'></head>
    <body>You appear to be already logged in.  If you are trying to log in to another account, 
    please log out and try again.</html>";
    echo $x;
    exit;
}
}
    

// if here, we are not have some kind of hybrid thing, just fob it off to the identity page
header("Location: ".$GLOBALS['Identity_Base_Url']."?id=$tracking");
echo "Redirecting to MedCommons Identity Service";
?>
