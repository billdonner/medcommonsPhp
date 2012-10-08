<?
/**
 * Main page - based on template
 */
require_once "../utils.inc.php";
require_once "template.inc.php";
$tpl = new Template($GLOBALS['layout_tpl_php']);
$tpl->set("loggedIn",is_logged_in());
$tpl->set("content", new Template("content.php"));
echo $tpl->fetch();
?>

