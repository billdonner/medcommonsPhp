<?
  require_once "utils.inc.php";

  $fileName = $_GET['f'];
  $fileNames = explode(',',$fileName);

  if(preg_match("/^.*\.css/",$fileNames[0])==1) {
    header('Content-Type: text/css');
  }
  else {
    header('Content-Type: text/javascript');
  }

  header('Cache-Control: public');
  header('Expires: Tue, 01 Jul 2025 00:00:00 GMT');
  ob_start("ob_gzhandler");

  foreach($fileNames as $fn) {
    include $fn;
  }
?>
