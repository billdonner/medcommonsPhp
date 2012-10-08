<?
require_once("setup.inc.php");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  error_log('setting cookie for source url '.$_REQUEST['src']);
  setcookie('src',$_REQUEST['src'], time()+3600);
  if (isset($_REQUEST['btk'])){
    error_log('setting cookie for source billing token '.$_REQUEST['btk']);
    setcookie ("btk", "", time() - 3600); // try to get rid of the cookie
  setcookie('btk',$_REQUEST['btk'], time()+3600);
  }

  header("Location: ".$GLOBALS['prod_url']);
?>
