<? 
  require_once "template.inc.php";
  require_once "utils.inc.php";
  check_clear_gw();
  echo template("widget.tpl.php")->set("content","<p>No Patient Details Available</p>")->fetch(); 
?>
