<?
require_once "urls.inc.php";
require_once "utils.inc.php";
nocache();
header("Content-Type: text/javascript");

set_current_gateway($_REQUEST['gw']);
?>
var gwRegisteredAt="<?=time()?>";
