<?
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");

  $src = null;
  if(isset($_POST['src']))
    $src = $_POST['src'];
  else
  if(isset($_GET['src']))
    $src = $_GET['src'];

  error_log('setting cookie for source url '.$src);
  setcookie('src',$src,time()+3600);

  $btk = isset($_GET['btk']) ? $_GET['btk'] : null;
  if($btk) {
    error_log('setting cookie for source billing token '.$btk);
    setcookie ("btk", "$btk", time() - 3600); // try to get rid of the cookie
    setcookie('btk',$btk, time()+3600);
  }

  header("Location: https://aws-portal.amazon.com/gp/aws/user/subscription/index.html?offeringCode=68A808C7");
?>
